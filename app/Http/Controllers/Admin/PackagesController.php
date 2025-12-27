<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Filters;
use App\Helpers\Invoice_Plan_Refund_Sms_Functions;
use App\Helpers\JazzSMSAPI;
use App\Helpers\TelenorSMSAPI;
use App\Helpers\Widgets\LocationsWidget;
use App\Helpers\Widgets\ServiceWidget;
use App\Models\Accounts;
use App\Models\Appointments;
use App\Models\AppointmentStatuses;
use App\Models\AppointmentTypes;
use App\Models\AuditTrailChanges;
use App\Models\AuditTrails;
use App\Models\BundleHasServices;
use App\Models\Bundles;
use App\Models\DiscountAllocation;
use App\Models\Leads;
use App\Models\LeadSources;
use App\Models\PackageSelling;
use App\Models\PackageService;
use App\Models\Promotion;
use App\Models\ServiceHasLocations;
use App\Models\Settings;
use App\Models\SMSLogs;
use App\Models\UserHasLocations;
use App\Models\UserOperatorSettings;
use Composer\Package\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use phpDocumentor\Reflection\Types\Integer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Validator;
use App\Models\Packages;
use App\Models\PackageBundles;
use App\Models\PackageAdvances;
use App\Models\Discounts;
use App\Models\Services;
use App\Models\SubscriptionCharge;
use App\Models\CardSubscription;
use App\User;
use Config;
use Carbon\Carbon;
use App\Models\PaymentModes;
use App\Models\Locations;
use App\Helpers\Widgets\DiscountWidget;
use App\Helpers\ACL;
use PDF;
use Illuminate\Support\Collection;
use App\Helpers\Financelog;
use App\Helpers\Widgets\PlanAppointmentCalculation;


class PackagesController extends Controller
{
    /**
     * Display a listing of the package.
     */
    public function index()
    {
        if (!Gate::allows('plans_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'packages');

        if ($user_id = Filters::get(Auth::User()->id, 'packages', 'patient_id')) {
            $patient = User::where(array(
                'id' => $user_id
            ))->first();
            if ($patient) {
                $patient = $patient->toArray();
            }
        } else {
            $patient = [];
        }

        if ($package_id = Filters::get(Auth::User()->id, 'packages', 'package_id')) {
            $package = Packages::where(array(
                'id' => $package_id
            ))->first();
            if ($package) {
                $package = $package->toArray();
            }
        } else {
            $package = [];
        }

        if ($package_selling_id = Filters::get(Auth::User()->id, 'packages', 'package_selling_id')) {
            $packageselling = PackageSelling::where(array(
                'id' => $package_selling_id
            ))->first();
            if ($packageselling) {
                $packageselling = $packageselling->toArray();
            }
        } else {
            $packageselling = [];
        }

        $locations = Locations::getActiveSorted(ACL::getUserCentres(), 'full_address');
        $locations->prepend('All', '');

        return view('admin.packages.index', compact('package', 'packageselling', 'locations', 'filters', 'patient'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        if (!Gate::allows('plans_create')) {
            return abort(401);
        }
       
        $locations = Locations::getActiveSorted(ACL::getUserCentres(), 'full_address');
        $locations->prepend('Select Centers', '');

        $random_id = md5(time() . rand(0001, 9999) . rand(78599, 99999));

        $unique_id = md5(time() . rand(0001, 9999) . rand(78599, 99999));

        $paymentmodes = PaymentModes::where('type', '=', 'application')->pluck('name', 'id');
        $paymentmodes->prepend('Select Payment Mode', '');

        $customdiscountrange = Settings::where('slug', '=', 'sys-discounts')->first();
        $range = explode(':', $customdiscountrange->data);

        $lead_sources = LeadSources::getActiveSorted();
        $lead_sources->prepend('Select a Lead Source', '');

        return view('admin.packages.create', compact('locations', 'random_id', 'paymentmodes', 'range', 'unique_id', 'lead_sources'));
    }

    /**
     * Return an array of location base service.
     */
    public function getservices(Request $request)
    {

        $service_has_location = ServiceHasLocations::where('location_id', '=', $request->location_id)->get();
        if ($service_has_location) {

            $locationhasservice = ServiceWidget::generateServicelcoationArray($service_has_location, session('account_id'));

            $locationinformation = Locations::find($request->location_id);

            return response()->json(array(
                'status' => true,
                'service' => $locationhasservice,
            ));
        } else {
            return response()->json(array(
                'status' => false,
            ));
        }
    }

    /**
     * get discount information.
     */
    public function getdiscountinfo(Request $request)
    {
        if ($request->discount_id) {

            $service_id = $request->service_id;
            $service_data = Bundles::find($service_id);

            $discount_id = $request->discount_id;
            $discount_data = Discounts::find($discount_id);

            if ($discount_data->slug == 'custom' || $discount_data->slug == 'special') {
                return response()->json(array(
                    'status' => true,
                    'slug' => 'custom',
                ));
            } else if ($discount_data->slug == 'periodic') {

                $service_data = Bundles::where('id', '=', $request->service_id)->first();

                $discount_allocations = DiscountAllocation::where([
                    ['discount_id', '=', $request->discount_id],
                    ['year', '=', Carbon::now()->format('Y')]
                ])->get();

                if (count($discount_allocations) > 0) {

                    foreach ($discount_allocations as $discount_allocation) {

                        $response = $this->getdiscountallocationcalculation($discount_allocation->user_id, $request->random_id, $request->unique_id, $discount_data);

                        $allocations[] = array(
                            'id' => $discount_allocation->id,
                            'name' => $discount_allocation->user->name . ' Total: ' . $response['total_avail_amount'] . ' Used: ' . $response['total_use_amount'] . ' Remaining: ' . $response['total_remaining_amount'],
                        );
                    }
                    return response()->json(array(
                        'status' => true,
                        'slug' => 'periodic',
                        'net_amount' => $service_data->price,
                        'references' => $allocations
                    ));
                } else {
                    return response()->json(array(
                        'status' => true,
                        'slug' => 'periodic',
                        'references' => array()
                    ));
                }
            } else {
                if ($discount_data->type == Config::get('constants.Fixed')) {
                    $discount_type = Config::get('constants.Fixed');
                    $discount_price = $discount_data->amount;
                    $net_amount = ($service_data->price) - ($discount_data->amount);
                } else {
                    $discount_type = Config::get('constants.Percentage');
                    $discount_price = $discount_data->amount;
                    $discount_price_cal = $service_data->price * (($discount_price) / 100);
                    $net_amount = ($service_data->price) - ($discount_price_cal);
                }
                return response()->json(array(
                    'status' => true,
                    'discount_type' => $discount_type,
                    'discount_price' => $discount_price,
                    'net_amount' => $net_amount,
                    'slug' => 'default'
                ));
            }
        } else {
            return response()->json(array(
                'status' => false,
            ));
        }
    }

    /**
     * save packages services information.
     */
    public function savepackages_service(Request $request)
    {
        // dd($request->all());
        $status = true;
        $service_data = Bundles::find($request->bundle_id);
        /*Total belongs to total Amount that increase when we enter new bundle*/
        $total = filter_var($request->package_total, FILTER_SANITIZE_NUMBER_INT);

        $setting_promotion = Settings::where('slug', '=', 'sys-signup-promotion')->first();
        $from_setting_discount_info = Discounts::where([
            ['name', '=', $setting_promotion->data],
            ['slug', '=', 'promotion']
        ])->first();

        $discountIds = array();
        $discountIds[] = $request->discount_id;

        if ($request->get('package_bundles')) {
            $package_bundles = PackageBundles::whereIn('id', $request->get('package_bundles'))->get();
            if ($package_bundles) {
                foreach ($package_bundles as $bundle) {
                    $discountIds[] = $bundle->discount_id;

                    if ($service_data->tax_treatment_type_id == 1) {
                        if ($bundle->bundle_id == $request->bundle_id && (floor($bundle->net_amount) != floor($request->net_amount) || $bundle->is_exclusive != (int)$request->is_exclusive)) {
                            $status = false;
                            return response()->json(array(
                                'status' => false,
                                'code' => 422,
                            ));
                        }
                    } else {
                        if ($bundle->bundle_id == $request->bundle_id && floor($bundle->net_amount) != floor($request->net_amount)) {
                            $status = false;
                            return response()->json(array(
                                'status' => false,
                                'code' => 422,
                            ));
                        }
                    }
                }

                $takendiscountcount = count($discountIds);
                $promotioncount = 0;

                foreach ($discountIds as $dis_id) {
                    if ($dis_id == $from_setting_discount_info->id) {
                        $promotioncount++;
                    }
                }
                if ($takendiscountcount == $promotioncount) {
                    $status = true;
                }
                if ($promotioncount == 0) {
                    $status = true;
                }
                if ($promotioncount > 0 && $promotioncount < $takendiscountcount) {
                    $status = false;
                    return response()->json(array(
                        'status' => false,
                        'code' => 423,
                    ));
                }
            }
        }

        if ($status == true) {
            /*First we need to make the data to save in package bundle*/
            $data = $request->all();
            $location_information = Locations::find($request->location_id);
            $discount_info = Discounts::find($request->discount_id);

            $data['qty'] = '1';
            $data['bundle_id'] = $service_data->id;
            $data['service_price'] = $service_data->price;

            if ($discount_info) {
                $data['discount_name'] = $discount_info->name;
                if ($discount_info->slug == 'periodic') {
                    $allocation_info = DiscountAllocation::find($request->reference_id);
                    $data['periodic_reference_id'] = $allocation_info->user_id;
                }
            }
            /*Checked it exclusive or not*/
            if ($service_data->tax_treatment_type_id == Config::get('constants.tax_both')) {
                if ($request->is_exclusive == '1') {
                    $data['tax_exclusive_net_amount'] = $request->net_amount;
                    $data['tax_percenatage'] = $location_information->tax_percentage;
                    $data['tax_price'] = ceil($data['tax_exclusive_net_amount'] * ($location_information->tax_percentage / 100));
                    $data['tax_including_price'] = ceil($data['tax_exclusive_net_amount'] + (($data['tax_exclusive_net_amount'] * $data['tax_percenatage']) / 100));

                    $data['is_exclusive'] = 1;
                } else {
                    $data['tax_including_price'] = $request->net_amount;
                    $data['tax_percenatage'] = $location_information->tax_percentage;
                    $data['tax_exclusive_net_amount'] = ceil((100 * $data['tax_including_price']) / ($data['tax_percenatage'] + 100));
                    $data['tax_price'] = ceil($data['tax_including_price'] - $data['tax_exclusive_net_amount']);

                    $data['is_exclusive'] = 0;
                }
            } else if ($service_data->tax_treatment_type_id == Config::get('constants.tax_is_exclusive')) {
                $data['tax_exclusive_net_amount'] = $request->net_amount;
                $data['tax_percenatage'] = $location_information->tax_percentage;
                $data['tax_price'] = ceil($data['tax_exclusive_net_amount'] * ($location_information->tax_percentage / 100));
                $data['tax_including_price'] = ceil($data['tax_exclusive_net_amount'] + (($data['tax_exclusive_net_amount'] * $data['tax_percenatage']) / 100));

                $data['is_exclusive'] = 1;
            } else {
                $data['tax_including_price'] = $request->net_amount;
                $data['tax_percenatage'] = $location_information->tax_percentage;
                $data['tax_exclusive_net_amount'] = ceil((100 * $data['tax_including_price']) / ($data['tax_percenatage'] + 100));
                $data['tax_price'] = ceil($data['tax_including_price'] - $data['tax_exclusive_net_amount']);

                $data['is_exclusive'] = 0;
            }
            /*In case If you not select any discount*/
            if ($request->discount_id == '0') {
                $data['discount_id'] = null;
            }
            /*date is develop to save package bundle*/

            /*Save package bundle information*/
            $packagesbundly = PackageBundles::createPackagebundle($data);

            /*Get the package service information*/
            $bundle_details = BundleHasServices::where('bundle_id', '=', $packagesbundly->bundle_id)->get();

            $calculable_servcies = array();

            foreach ($bundle_details as $detail) {
                $calculable_servcies[] = array(
                    'service_price' => $detail->calculated_price,
                    'calculated_price' => $detail->calculated_price,
                    'service_id' => $detail->service_id,
                );
            }
            /*calculate price of services according to their prices*/
            $calculated_services = Bundles::calculatePrices($calculable_servcies, $data['service_price'], $data['net_amount']);

            /*Second we need to make the data to save in package services*/
            foreach ($calculated_services as $detail) {

                $data_service['random_id'] = $request->random_id;
                $data_service['package_bundle_id'] = $packagesbundly->id;
                $data_service['service_id'] = $detail['service_id'];
                $data_service['price'] = $detail['calculated_price'];
                $data_service['orignal_price'] = $detail['service_price'];

                /*Checked it exclusive or not*/
                if ($service_data->tax_treatment_type_id == Config::get('constants.tax_both')) {
                    if ($request->is_exclusive == '1') {
                        $data_service['tax_exclusive_price'] = $detail['calculated_price'];
                        $data_service['tax_percenatage'] = $location_information->tax_percentage;
                        $data_service['tax_price'] = ceil($detail['calculated_price'] * ($location_information->tax_percentage / 100));
                        $data_service['tax_including_price'] = ceil($data_service['tax_exclusive_price'] + (($data_service['tax_exclusive_price'] * $data_service['tax_percenatage']) / 100));

                        $data_service['is_exclusive'] = 1;
                    } else {
                        $data_service['tax_including_price'] = $detail['calculated_price'];
                        $data_service['tax_percenatage'] = $location_information->tax_percentage;
                        $data_service['tax_exclusive_price'] = ceil((100 * $data_service['tax_including_price']) / ($data_service['tax_percenatage'] + 100));
                        $data_service['tax_price'] = ceil($data_service['tax_including_price'] - $data_service['tax_exclusive_price']);

                        $data_service['is_exclusive'] = 0;
                    }
                } else if ($service_data->tax_treatment_type_id == Config::get('constants.tax_is_exclusive')) {
                    $data_service['tax_exclusive_price'] = $detail['calculated_price'];
                    $data_service['tax_percenatage'] = $location_information->tax_percentage;
                    $data_service['tax_price'] = ceil($detail['calculated_price'] * ($location_information->tax_percentage / 100));
                    $data_service['tax_including_price'] = ceil($data_service['tax_exclusive_price'] + (($data_service['tax_exclusive_price'] * $data_service['tax_percenatage']) / 100));

                    $data_service['is_exclusive'] = 1;
                } else {
                    $data_service['tax_including_price'] = $detail['calculated_price'];
                    $data_service['tax_percenatage'] = $location_information->tax_percentage;
                    $data_service['tax_exclusive_price'] = ceil((100 * $data_service['tax_including_price']) / ($data_service['tax_percenatage'] + 100));
                    $data_service['tax_price'] = ceil($data_service['tax_including_price'] - $data_service['tax_exclusive_price']);

                    $data_service['is_exclusive'] = 0;
                }
                $packageservice = PackageService::createPackageService($data_service);
            }
            /*calculate package value to return*/
            $total = number_format($total + $packagesbundly->tax_including_price);

            /*Set variables for return to show information*/
            $net_amount = $packagesbundly->net_amount;
            $service_name = $packagesbundly->bundle->name;
            $service_price = $packagesbundly->service_price;

            /*use user giving attributes for custom package*/

            if ($request->discount_id == '0' || $request->discount_id == null) {
                $discount_name = '-';
                $discount_type = '-';
                $discount_price = '0.00';
            } else {
                $discount_name = $packagesbundly->discount_name;
                $discount_type = $packagesbundly->discount_type;
                $discount_price = $packagesbundly->discount_price;
            }
            $package_service = Services::join('package_services', 'services.id', '=', 'package_services.service_id')
                ->select('package_services.*', 'services.name')
                ->where('package_services.package_bundle_id', '=', $packagesbundly->id)
                ->get();
            $package_bundles = PackageBundles::find($packagesbundly->id);
            $myarray = [
                'record' => $package_bundles,
                'record_detail' => $package_service,
                'random_id' => $request->random_id,
                'service_name' => $service_name,
                'service_price' => $service_price,
                'discount_name' => $discount_name,
                'discount_type' => $discount_type,
                'discount_price' => $discount_price,
                'net_amount' => $net_amount,
                'total' => $total
            ];

            return response()->json(array(
                'status' => true,
                'code' => 200,
                'myarray' => $myarray,

            ));
        }
    }

    /**
     * get discount information for custom package.
     */
    public function getdiscountinfocustom(Request $request)
    {
        // dd($request->all());
        $status = true;

        $service_data = Bundles::find($request->service_id);
        // $subscription_discount =
        // (
        // ( 
        //    SubscriptionCharge::where('account_id',auth()->user()->account_id)->first() 
        // && SubscriptionCharge::where('account_id',auth()->user()->account_id)->first()->offered_discount
        // ) 
        //    ? SubscriptionCharge::where('account_id',auth()->user()->account_id
        //    )
        //    ->first()->offered_discount * $service_data->price 
        //    : 0
        // );

        $discount_data = Discounts::find($request->discount_id);

        if ($request->discount_type == Config::get('constants.Fixed')) {

            $discount_price = $request->discount_value;

            $discount_price_in_percentage = ($discount_price / $service_data->price) * 100;

            if ($discount_data->amount >= $discount_price_in_percentage) {

                $net_amount = ceil(($service_data->price) - ($discount_price));
            } else {
                $status = false;
            }
        } else {

            $discount_price = $request->discount_value;

            if ($discount_data->amount >= $discount_price) {

                $discount_price_cal = $service_data->price * (($discount_price) / 100);

                $net_amount = ceil(($service_data->price) - ($discount_price_cal));
            } else {
                $status = false;
            }
        }
        if ($status == true) {
            return response()->json(array(
                'status' => true,
                'net_amount' => $net_amount,
                // 'subscription_discount' => $subscription_discount
            ));
        } else {
            return response()->json(array(
                'status' => false,
            ));
        }
    }

    /**
     * delete serive from packages
     *
     * @param request
     */
    public function deletepackagesservice(Request $request)
    {
        $status = PackageService::where([
            ['package_bundle_id', '=', $request->id],
            ['is_consumed', '=', '1']
        ])->first();
        if ($status) {
            return response()->json(array(
                'status' => false,
            ));
        } else {

            $packageService = PackageBundles::find($request->id);

            $package_total = filter_var($request->package_total, FILTER_SANITIZE_NUMBER_INT);

            $total = $package_total - $packageService->tax_including_price;

            PackageService::where('package_bundle_id', '=', $request->id)->delete();

            PackageBundles::find($request->id)->forcedelete();

            if ($request->update_status == 1) {
                if ($packageService->package_id) {
                    $record = Packages::find($packageService->package_id);
                    $record->update(['total_price' => $total]);
                }
            }

            return response()->json(array(
                'status' => true,
                'total' => $total,
                'id' => $request->id
            ));
        }
    }

    /**
     * delete serive from packages
     *
     * @param request
     */
    public function deletepackagesexclusive(Request $request)
    {
        $data = $request->all();
        if (isset($data['random_id']) && $data['random_id']) {
            PackageService::where('random_id', '=', $request->random_id)->forcedelete();
            PackageBundles::where('random_id', '=', $request->random_id)->forcedelete();

            return response()->json(array(
                'status' => true
            ));
        }

        return response()->json(array(
            'status' => false
        ));
    }

    /**
     * save package
     *
     * @param request
     */
    public function savepackages(Request $request)
    {
        $promotion_status = false;

        if ($request->grand_total < 0) {
            return response()->json(array(
                'status' => false,
                'code' => 422
            ));
        }
        // Check if plan belong to promotion so need complete amount
        $setting_promotion = Settings::where('slug', '=', 'sys-signup-promotion')->first();

        $from_setting_discount_info = Discounts::where([
            ['name', '=', $setting_promotion->data],
            ['slug', '=', 'promotion']
        ])->first();

        $package_bundles = PackageBundles::whereIn('id', $request->get('package_bundles'))->get();

        if ($package_bundles[0]['discount_id'] == $from_setting_discount_info->id) {
            $total = filter_var($request->total, FILTER_SANITIZE_NUMBER_INT);
            $cash_amount = filter_var($request->cash_amount, FILTER_SANITIZE_NUMBER_INT);
            if ($total != $cash_amount) {
                return response()->json(array(
                    'status' => false,
                    'code' => 423
                ));
            } else {
                $promotion_status = true;
                $promotion_discount_id = $from_setting_discount_info->id;
                $promotion_user_id = $request->patient_id;
            }
        }

        // Begin Transaction
        DB::beginTransaction();

        try {
            if (isset($request->appointment_id)) {
                // Now we need to work our tag appointment for upselling
                $tag_appoint = explode('.', $request->appointment_id);

                if ($tag_appoint[1] == 'A') {
                    $appointment_id = $tag_appoint[0];
                } else {
                    $PlanAppointmentCalculation = new PlanAppointmentCalculation();
                    $appointment_id = $PlanAppointmentCalculation->storeAppointment($request->patient_id, $request->location_id, $request, $tag_appoint[0], false);
                    $PlanAppointmentCalculation->saveinvoice($appointment_id);
                }
            } else {
                return response()->json(array(
                    'status' => false,
                    'code' => 422
                ));
            }
            /*save Package information and also update random id in package service table*/

            $data_package = $request->all();
            $data_package['total_price'] = filter_var($request->total, FILTER_SANITIZE_NUMBER_INT);
            $data_package['sessioncount'] = '1';
            $data_package['account_id'] = session('account_id');
            $data_package['is_exclusive'] = $request->is_exclusive;
            $data_package['appointment_id'] = $appointment_id;

            $package = Packages::createRecord($data_package, $request);

            if ($package) {
                $appointment = \App\Models\Appointments::where('id', $appointment_id)->first();
                if ($package->total_price > 0) {
                    $appointment->update(['is_converted' => 1]);
                }
                if ($promotion_status) {
                    Promotion::where([
                        ['discount_id', '=', $promotion_discount_id],
                        ['user_id', '=', $promotion_user_id]
                    ])->update(['use' => 'Yes']);
                }
            }
            /*End*/
            if ($request->cash_amount == '0') {
                // Commit Transaction
                DB::commit();

                return response()->json(array(
                    'status' => true,
                    'code' => 200
                ));
            } else {
                /*Save data in package advances*/
                $data_packageAdvances['cash_flow'] = 'in';
                $data_packageAdvances['cash_amount'] = $request->cash_amount;
                $data_packageAdvances['account_id'] = session('account_id');
                $data_packageAdvances['patient_id'] = $request->patient_id;
                $data_packageAdvances['payment_mode_id'] = $request->payment_mode_id;
                $data_packageAdvances['created_by'] = Auth::User()->id;
                $data_packageAdvances['updated_by'] = Auth::User()->id;
                $data_packageAdvances['package_id'] = $package->id;
                $data_packageAdvances['location_id'] = $request->location_id;
                /*End*/
                $packageAdavances = PackageAdvances::createRecord($data_packageAdvances, $package);
                if ($packageAdavances) {
                    $appointment = \App\Models\Appointments::where('id', $packageAdavances->appointment_id)
                        ->update(['is_converted' => 1]);
                }
                /*Now sent message to user about cash received*/
                Invoice_Plan_Refund_Sms_Functions::PlanCashReceived_SMS($package->id, $packageAdavances);
                // Commit Transaction
                DB::commit();
                // dd($request->all());
                return response()->json(array(
                    'status' => true,
                    'code' => 200
                ));
            }
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            return response()->json(array(
                'status' => false,
                'code' => 422,
                'message' => $e->getMessage(),
            ));
        }
    }

    /**
     * Get service info
     *
     * @param request
     *
     * @return mixed
     */
    public function getserviceinfo(Request $request)
    {
        /*because now we not give any discount to package if package have no permission to use. for this we introduce that empty collection */
        $discounts = Collection::make();
        /*end*/
        $today = Carbon::now()->toDateString();

        $bundle = Bundles::find($request->bundle_id);

        if ($bundle->type == 'single') {

            $bundleService = BundleHasServices::where(array(
                'bundle_id' => $bundle->id
            ))->first();

            $service_id = $bundleService->service_id;

            $location_id = $request->location_id;

            $discountIds = DiscountWidget::loadPlanDsicountByLocationService($location_id, $service_id, Auth::User()->account_id);

            $discounts = Discounts::whereIn('id', $discountIds)->where([
                ['discount_type', '=', 'Treatment'],
                ['active', '=', '1']
            ])->whereDate('start', '<=', $today)->whereDate('end', '>=', $today)->get();
        } else {
            if ($bundle->apply_discount == '1') {
                $bundleServices = BundleHasServices::where(array(
                    'bundle_id' => $bundle->id
                ))->get();
                foreach ($bundleServices as $bundleService) {
                    $service_id = $bundleService->service_id;
                    $location_id = $request->location_id;
                    $discountIds[] = DiscountWidget::loadPlanDsicountByLocationService($location_id, $service_id, Auth::User()->account_id);
                }
                $uniq_array = [];
                foreach ($discountIds as $discountId) {
                    foreach ($discountId as $singledata) {
                        if (!in_array($singledata, $uniq_array)) {
                            $uniq_array[] = $singledata;
                        }
                    }
                }
                $discounts = Discounts::whereIn('id', $uniq_array)->where([
                    ['discount_type', '=', 'Treatment'],
                    ['active', '=', '1']
                ])->whereDate('start', '<=', $today)->whereDate('end', '>=', $today)->get();
            }
        }

        $temp_discounts = [];

        /*Now Checked Brithday promotion valid or not*/
        foreach ($discounts as $key => $discount) {
            // Here is the code for promotion signup thing
            $setting_promotion = Settings::where('slug', '=', 'sys-signup-promotion')->first();

            if ($discount->slug == 'promotion' && $discount->name == $setting_promotion->data) {
                $promotion = Promotion::where([
                    ['user_id', '=', $request->patient_id],
                    ['use', '=', 'No']
                ])->first();
                if (!$promotion) {
                    $discounts->forget($key);
                }
            }
            // End
            if ($discount->slug == 'birthday') {
                /*first get the pre and post days*/
                $pre_days = $discount->pre_days;
                $post_days = $discount->post_days;
                /*end*/

                $today_1 = Carbon::today();
                $today_2 = Carbon::today();
                $today_3 = Carbon::today();

                /*get the date range to checked patient birthday exist between or not*/
                $predate = $today_1->subDay($pre_days)->format('Y-m-d');
                $postdate = $today_2->addDay($post_days)->format('Y-m-d');

                $patient_info = User::find($request->patient_id);

                /*Now checked birthday valid or not*/
                if ($patient_info->dob) {

                    $patientbirthday = Carbon::parse($patient_info->dob)->format($today_3->year . '-' . 'm-d');

                    if (($patientbirthday >= $predate) && ($patientbirthday <= $postdate)) {
                    } else {
                        $discounts->forget($key);
                    }
                } else {
                    $discounts->forget($key);
                }
            }
        }
        /*end*/
        $service_data = Bundles::where('id', '=', $request->bundle_id)->first();
        // $subscription_discount = CardSubscription::where('patient_id',$request->patient_id)->
            // where('expiry_date','>=', date('Y-m-d H:i:s'))->first();
        if (count($discounts) > 0) {
            $discounts = $discounts->toArray();
            return response()->json(array(
                'status' => true,
                'discounts' => $discounts,
                'net_amount' => $service_data->price,
            //     'subscription_discount' =>$subscription_discount 
            //     ? ((SubscriptionCharge::where('account_id',auth()->user()->account_id)->first() && SubscriptionCharge::where('account_id',auth()->user()->account_id)->first()->offered_discount) 
            //     ? SubscriptionCharge::where('account_id',auth()->user()->account_id)->first()->offered_discount * $service_data->price 
            //     : 0)
            // : 0,
            ));
        } else {
            return response()->json(array(
                'status' => false,
                'net_amount' => $service_data->price,
            //     'subscription_discount' =>$subscription_discount 
            //     ? ((SubscriptionCharge::where('account_id',auth()->user()->account_id)->first() && SubscriptionCharge::where('account_id',auth()->user()->account_id)->first()->offered_discount) 
            //     ? SubscriptionCharge::where('account_id',auth()->user()->account_id)->first()->offered_discount * $service_data->price 
            //     : 0)
            // : 0,
            ));
        }
    }

    /**
     * Get service info whan discount not selected
     *
     * @param request
     *
     * @return mixed
     */
    public function getservices_for_zero(Request $request)
    {

        $service_data = Bundles::where('id', '=', $request->bundle_id)->first();
        if ($service_data) {
            return response()->json(array(
                'status' => true,
                'net_amount' => $service_data->price,
            //     'subscription_discount' =>
            //     (
            //     ( 
            //        SubscriptionCharge::where('account_id',auth()->user()->account_id)->first() 
            //     && SubscriptionCharge::where('account_id',auth()->user()->account_id)->first()->offered_discount
            //     ) 
            //        ? SubscriptionCharge::where('account_id',auth()->user()->account_id
            //        )
            //        ->first()->offered_discount * $service_data->price 
            //        : 0
            //    ),
            ));
        } else {
            return response()->json(array(
                'status' => false,
            ));
        }
    }

    /**
     * calculate the grand total
     *
     * @param request
     *
     * @return mixed
     */
    public function getgrandtotal(Request $request)
    {
        $package_total = filter_var($request->total, FILTER_SANITIZE_NUMBER_INT);
        $grand_total = number_format($package_total - $request->cash_amount);

        return response()->json(array(
            'status' => true,
            'grand_total' => $grand_total
        ));
    }

    /**
     * Display a User As package in datatables.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $filename = 'packages';
        $apply_filter = false;
        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, $filename);
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $packages = Packages::getBulkData($request->get('id'));
            $any_deleted = false;
            if ($packages) {
                foreach ($packages as $package) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!Packages::isChildExists($package->id, Auth::User()->account_id)) {
                        $any_deleted = true;
                        $package->delete();
                    }
                }
            }
            if ($any_deleted) {
                $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
                $records["customActionMessage"] = "One or more record has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
            } else {
                $records["customActionStatus"] = "NO"; // pass custom message(useful for getting status of group actions)
                $records["customActionMessage"] = "Chalid records exist, unable to delete plan!"; // pass custom message(useful for getting status of group actions)
            }
        }

        // Get Total Records
        $iTotalRecords = Packages::getTotalRecords($request, Auth::User()->account_id, false, $apply_filter, $filename);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $packages = Packages::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, false, $apply_filter, $filename);

        if ($packages) {
            foreach ($packages as $package) {
                $session_count = count(PackageBundles::where('package_id', '=', $package->id)->get());
                /*We discuss in future what happen next*/
                $cash_receive = PackageAdvances::where([
                    ['package_id', '=', $package->id],
                    ['cash_flow', '=', 'in'],
                    ['is_cancel', '=', '0']
                ])->sum('cash_amount');
                if ($package->is_refund == '0') {
                    $refund_status = 'No';
                } else {
                    $refund_status = 'Yes';
                }
                // dd($package);
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $package->id . '"/><span></span></label>',
                    'name' => $package->user->name,
                    'package_id' => $package->name,
                    'location_id' => $package->location->city->name . "-" . $package->location->name,
                    'session_count' => $session_count,
                    'total' => number_format($package->total_price),
                    'cash_receive' => number_format($cash_receive),
                    'refund' => $refund_status,
                    'package_selling_id' => $package->package_selling_id,
                    'created_at' => Carbon::parse($package->created_at)->format('F j,Y h:i A'),
                    'status' => view('admin.packages.status', compact('package'))->render(),
                    'actions' => view('admin.packages.actions', compact('package'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('plans_inactive')) {
            return abort(401);
        }

        Packages::inactiveRecord($id);

        return redirect()->route('admin.packages.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('plans_active')) {
            return abort(401);
        }
        Packages::activeRecord($id);

        return redirect()->route('admin.packages.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('plans_edit')) {
            return abort(401);
        }
        $package = Packages::find($id);

        /*Due to finance editing we calculate that "total" through package bundle otherwise we can use package->total_amount*/
        $total_price = PackageBundles::where('package_id', '=', $id)->sum('tax_including_price');

        $packagebundles = PackageBundles::where('package_id', '=', $package->id)->get();
        $packageservices = PackageService::where('package_id', '=', $package->id)->get();

        $packageadvances = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['is_cancel', '=', '0'],
            ['is_refund', '=', '0']
        ])->get();

        $user_has_location = UserHasLocations::where('user_id', '=', Auth::User()->id)->get()->toArray();
        if ($user_has_location) {
            foreach ($user_has_location as $userhaslocation) {
                $location = Locations::where([
                    ['id', '=', $userhaslocation['location_id']],
                    ['account_id', '=', Auth::User()->account_id]
                ])->first();
                if ($location->slug == 'custom') {
                    $locations[] = $location;
                }
            }
        } else {
            $locations = [];
        }

        $cash_amount_in = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'in'],
            ['is_cancel', '=', '0']
        ])->sum('cash_amount');

        $cash_amount_out = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'out']
        ])->sum('cash_amount');

        $cash_amount = $cash_amount_in - $cash_amount_out;
        /*We discuss it in future what happen next*/
        $grand_total = number_format($total_price - $cash_amount_in);

        $paymentmodes = PaymentModes::where('type', '=', 'application')->pluck('name', 'id');
        $paymentmodes->prepend('Select Payment Mode', '');

        $customdiscountrange = Settings::where('slug', '=', 'sys-discounts')->first();

        $range = explode(':', $customdiscountrange->data);

        $service_has_location = ServiceHasLocations::where('location_id', '=', $package->location_id)->get();

        if ($service_has_location) {
            $locationhasservice = ServiceWidget::generateServicelcoationArray($service_has_location, session('account_id'));
        }

        $finance_editing_days = Settings::where('slug', '=', 'sys-financeediting')->first();

        $end_previous_date = Carbon::now()->subDays($finance_editing_days->data)->toDateString();

        $data['patient_id'] = $package->patient_id;

        $data['location_id'] = $package->location_id;

        $data = (object)$data;

        $appointmentArray = PlanAppointmentCalculation::tagAppointments($data);

        $unique_id = md5(time() . rand(0001, 9999) . rand(78599, 99999));

        $lead_sources = LeadSources::getActiveSorted();
        $lead_sources->prepend('Select a Lead Source', '');

        return view('admin.packages.edit', compact('package', 'locations', 'packagebundles', 'packageservices', 'packageadvances', 'paymentmodes', 'grand_total', 'range', 'locationhasservice', 'total_price', 'end_previous_date', 'appointmentArray', 'unique_id', 'lead_sources'));
    }

    /**
     * calculate the grand total
     *
     * @param request
     *
     * @return mixed
     */
    public function getgrandtotal_update(Request $request)
    {
        $package = Packages::where('random_id', '=', $request->random_id)->first();

        $package_advances_cash_amount_1 = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'in'],
            ['is_cancel', '=', '0']
        ])->sum('cash_amount');

        $package_advances_cash_amount_2 = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'out']
        ])->sum('cash_amount');
        /*We discuss in future what happen next*/
        $package_advances_cash_amount = $package_advances_cash_amount_1;

        $package_total = filter_var($request->total, FILTER_SANITIZE_NUMBER_INT);
        $grand_total = number_format(($package_total - $package_advances_cash_amount) - $request->cash_amount);

        return response()->json(array(
            'status' => true,
            'grand_total' => $grand_total
        ));
    }

    /**
     * Update package
     * @param $request
     * @return mixed
     * */
    public function updatepackages(Request $request)
    {
        $promotion_status = false;

        if ($request->grand_total < 0) {
            return response()->json(array(
                'status' => false,
                'code' => 422
            ));
        }

        // Check if plan belong to promotion so need complete amount
        $setting_promotion = Settings::where('slug', '=', 'sys-signup-promotion')->first();

        $from_setting_discount_info = Discounts::where([
            ['name', '=', $setting_promotion->data],
            ['slug', '=', 'promotion']
        ])->first();

        $package_bundles = PackageBundles::whereIn('id', $request->get('package_bundles'))->get();

        if ($package_bundles[0]['discount_id'] == $from_setting_discount_info->id) {
            $total = filter_var($request->total, FILTER_SANITIZE_NUMBER_INT);
            $cash_amount = filter_var($request->cash_amount, FILTER_SANITIZE_NUMBER_INT);
            $package_info_tag = Packages::where('random_id', '=', $request->random_id)->first();
            $packageadvances = PackageAdvances::where([
                ['package_id', '=', $package_info_tag->id],
                ['is_cancel', '=', '0'],
                ['is_refund', '=', '0']
            ])->get();
            if ($packageadvances) {
                foreach ($packageadvances as $packageadvance) {
                    if ($packageadvance->cash_amount != '0' && $packageadvance->cash_flow == 'in') {
                        $cash_amount += $packageadvance->cash_amount;
                    }
                }
            }
            if ($total != $cash_amount) {
                return response()->json(array(
                    'status' => false,
                    'code' => 423
                ));
            } else {
                $promotion_status = true;
                $promotion_discount_id = $from_setting_discount_info->id;
                $promotion_user_id = $request->patient_id;
            }
        }

        // Begin Transaction
        DB::beginTransaction();

        try {
            if (isset($request->appointment_id)) {
                // Now we need to work our tag appointment for upselling
                $tag_appoint = explode('.', $request->appointment_id);

                if ($tag_appoint[1] == 'A') {
                    $appointment_id = $tag_appoint[0];
                } else {
                    $PlanAppointmentCalculation = new PlanAppointmentCalculation();
                    $package_info_tag = Packages::where('random_id', '=', $request->random_id)->first();
                    $appointment_decision = Appointments::find($package_info_tag->appointment_id);
                    if (isset($appointment_decision)) {
                        $appointment_id = $PlanAppointmentCalculation->updateAppointment($request->patient_id, $request->location_id, $request, $tag_appoint[0], $package_info_tag);
                    } else {
                        $appointment_id = $PlanAppointmentCalculation->storeAppointment($request->patient_id, $request->location_id, $request, $tag_appoint[0], false);
                        $PlanAppointmentCalculation->saveinvoice($appointment_id);
                    }
                }
            } else {
                return response()->json(array(
                    'status' => false,
                    'code' => 422
                ));
            }
            /*save Package information and also update random id in package service table*/

            $data_package = $request->all();
            $data_package['total_price'] = filter_var($request->total, FILTER_SANITIZE_NUMBER_INT);
            $data_package['sessioncount'] = '1';
            $data_package['account_id'] = session('account_id');
            $data_package['appointment_id'] = $appointment_id;

            $random_id = $request->random_id;

            $package = Packages::updateRecord($data_package, $random_id, $request);

            if ($package) {
                if ($promotion_status) {
                    Promotion::where([
                        ['discount_id', '=', $promotion_discount_id],
                        ['user_id', '=', $promotion_user_id]
                    ])->update(['use' => 'Yes']);
                }
            }

            /*End*/
            if ($request->cash_amount == '0') {

                // Commit Transaction
                DB::commit();

                return response()->json(array(
                    'status' => true,
                    'code' => 200
                ));
            } else {
                /*Save data in package advances*/
                $data_packageAdvances['cash_flow'] = 'in';
                $data_packageAdvances['cash_amount'] = $request->cash_amount;
                $data_packageAdvances['account_id'] = session('account_id');
                $data_packageAdvances['patient_id'] = $request->patient_id;
                $data_packageAdvances['payment_mode_id'] = $request->payment_mode_id;
                $data_packageAdvances['created_by'] = Auth::User()->id;
                $data_packageAdvances['updated_by'] = Auth::User()->id;
                $data_packageAdvances['package_id'] = $package->id;
                $data_packageAdvances['location_id'] = $request->location_id;
                /*End*/

                $packageAdavances = PackageAdvances::updateRecord($data_packageAdvances, $package);

                /*Now sent message to user about cash received*/
                Invoice_Plan_Refund_Sms_Functions::PlanCashReceived_SMS($package->id, $packageAdavances);

                // Commit Transaction
                DB::commit();

                return response()->json(array(
                    'status' => true,
                    'code' => 200
                ));
            }
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();

            return response()->json(array(
                'status' => false,
                'code' => 422
            ));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('plans_destroy')) {
            return abort(401);
        }

        Packages::deleteRecord($id);

        return redirect()->route('admin.packages.index');
    }

    /**
     * display the package.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function display($id)
    {
        if (!Gate::allows('plans_manage')) {
            return abort(401);
        }

        $package = Packages::find($id);

        $packagebundles = PackageBundles::where('package_id', '=', $package->id)->get();

        $packageservices = PackageService::where('package_id', '=', $package->id)->get();

        $packageadvances = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['is_cancel', '=', '0'],
            ['is_refund', '=', '0']
        ])->get();

        $cash_amount_in = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'in']
        ])->sum('cash_amount');

        $cash_amount_out = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'out']
        ])->sum('cash_amount');

        $cash_amount = $cash_amount_in - $cash_amount_out;

        /*We discuss it in future what happen next*/

        $grand_total = number_format($package->total_price - $cash_amount_in);

        $services = Services::getServices();
        $discount = Discounts::getDiscount(session('account_id'));
        $paymentmodes = PaymentModes::get()->pluck('name', 'id');
        $paymentmodes->prepend('Select Payment Mode', '');

        return view('admin.packages.display', compact('package', 'packagebundles', 'packageservices', 'packageadvances', 'services', 'discount', 'paymentmodes', 'grand_total'));
    }

    /**
     * Print the package.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function package_pdf($id)
    {

        if (!Gate::allows('plans_manage')) {
            return abort(401);
        }
        $package = Packages::find($id);

        $location_info = Locations::find($package->location_id);

        $account_info = Accounts::find($package->account_id);

        $packagebundles = PackageBundles::where('package_id', '=', $package->id)->get();

        $packageservices = PackageService::where('package_id', '=', $package->id)->get();

        $packageadvances = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['is_cancel', '=', '0'],
            ['is_refund', '=', '0']
        ])->get();

        $cash_amount_in = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'in']
        ])->sum('cash_amount');

        $cash_amount_out = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'out']
        ])->sum('cash_amount');

        $cash_amount = $cash_amount_in - $cash_amount_out;
        /*We discuss it in future what happen next*/
        $grand_total = number_format($package->total_price - $cash_amount_in);

        $services = Services::getServices();
        $discount = Discounts::getDiscount(session('account_id'));

        $paymentmodes = PaymentModes::get()->pluck('name', 'id');
        $paymentmodes->prepend('Select Payment Mode', '');

        $company_phone_number = Settings::where('slug', '=', 'sys-headoffice')->first();

        $content = view('admin.packages.packagepdf', compact('package', 'packagebundles', 'packageservices', 'packageadvances', 'services', 'discount', 'paymentmodes', 'grand_total', 'location_info', 'account_info', 'company_phone_number'));
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($content);
        return $pdf->stream('Treatment Plans Invoice');
    }

    /**
     * $edit the cash that enter in package advances
     */
    public function editpackageadvancescashindex($id, $package_id)
    {
        $pack_adv_info = PackageAdvances::find($id);

        $paymentmodes = PaymentModes::where('type', '=', 'application')->get();

        return view('admin.packages.finance_edit.create', compact('pack_adv_info', 'package_id', 'paymentmodes'));
    }

    /**
     * Store the cash that is request to change
     */
    public function storepackageadvancescash(Request $request)
    {
        $package_total_price = PackageBundles::where('package_id', '=', $request->package_id)->sum('tax_including_price');

        $get_package_use_amount = PackageAdvances::where([
            ['package_id', '=', $request->package_id],
            ['cash_flow', '=', 'out']
        ])->sum('cash_amount');

        $get_package_unused_amount_except_edit = PackageAdvances::where([
            ['id', '!=', $request->package_advances_id],
            ['package_id', '=', $request->package_id],
            ['cash_flow', '=', 'in'],
            ['is_cancel', '=', '0']
        ])->sum('cash_amount');

        $get_package_unused_amount_with_edit = $request->cash_amount;

        $get_package_unuse_amount = $get_package_unused_amount_except_edit + $get_package_unused_amount_with_edit;

        if ($get_package_unuse_amount <= $package_total_price) {
            if ($get_package_unuse_amount >= $get_package_use_amount) {
                $amount_status = true;
            } else {
                $amount_status = false;
            }
            $record = PackageAdvances::updateRecordFinanceedit($request, Auth::User()->account_id, $amount_status);
        } else {
            $record = Null;
        }

        if ($record) {
            return response()->json(array(
                'status' => true,
                'amount_status' => $amount_status
            ));
        } else {
            return response()->json(array(
                'status' => false,
            ));
        }
    }

    /**
     * Delete the cash that reqquire to delete
     */
    public function deletepackageadvancescash(Request $request)
    {
        $packageadvanceinfo = PackageAdvances::withTrashed()->find($request->package_advance_id);

        $get_package_use_amount = PackageAdvances::where([
            ['package_id', '=', $packageadvanceinfo->package_id],
            ['cash_flow', '=', 'out']
        ])->sum('cash_amount');
        $get_package_unused_amount_except_edit = PackageAdvances::where([
            ['id', '!=', $request->package_advance_id],
            ['package_id', '=', $packageadvanceinfo->package_id],
            ['cash_flow', '=', 'in']
        ])->sum('cash_amount');
        if ($get_package_use_amount <= $get_package_unused_amount_except_edit) {

            $status = true;
            $record = PackageAdvances::deletefinaceRecord($request);
            $cash_receveive_remain = number_format(filter_var($request->cash_receveive_remain, FILTER_SANITIZE_NUMBER_INT) + $packageadvanceinfo->cash_amount);

            return response()->json(array(
                'status' => $status,
                'id' => $request->package_advance_id,
                'cash_receveive_remain' => $cash_receveive_remain
            ));
        } else {
            return response()->json(array(
                'status' => $status = false,
            ));
        }
    }

    /**
     *  Get the information of appointment against
     */
    public function getappointmentinfo(Request $request)
    {
        $appointmentArray = PlanAppointmentCalculation::tagAppointments($request);

        if (count($appointmentArray) > 0) {
            return response()->json(array(
                'status' => true,
                'data' => $appointmentArray,
            ));
        } else {
            return response()->json(array(
                'status' => false,
                'data' => $appointmentArray,
            ));
        }
    }

    /**
     *  Function for log for package
     */
    public function packagelog($id, $type)
    {
        if (!Gate::allows('plans_log')) {
            return abort(401);
        }
        $action_array = array(
            1 => 'Create',
            2 => 'Edit',
            3 => 'Delete',
            4 => 'Inactive',
            5 => 'Active',
            6 => 'Cancel',
        );
        $table_array = array(
            25 => 'Finance'
        );
        $finance_log = array();

        $find_ids = PackageAdvances::withTrashed()->where('package_id', '=', $id)->pluck('id')->toArray();

        array_push($find_ids, $id);

        $audittrails = AuditTrails::whereIn('table_record_id', $find_ids)->where('audit_trail_table_name', '=', Config::get('constants.package_advance_table_name_log'))->orderBy('created_at', 'asc')->get();

        $count = 1;
        foreach ($audittrails as $audittrail) {
            $finance_log[$audittrail->id] = array(
                'sr no' => $count++,
                'id' => $audittrail->id,
                'action' => $action_array[$audittrail->audit_trail_action_name],
                'table' => $table_array[$audittrail->audit_trail_table_name],
                'user_id' => $audittrail->user->name,
                'created_at_orignal' => $audittrail->created_at,
                'updated_at_orignal' => $audittrail->updated_at,
                'detail_log' => array(),

            );

            $audittrail_changes = AuditTrailChanges::where('audit_trail_id', '=', $audittrail->id)->get();

            foreach ($audittrail_changes as $changes) {
                if ($action_array[$audittrail->audit_trail_action_name] == 'Delete') {
                    if ($changes->field_name == 'cash_amount' || $changes->field_name == 'deleted_at') {
                        $result = Financelog::Calculate_Val_advance($changes);
                        $finance_log[$audittrail->id][$changes->field_name] = $result;
                    }
                } else {
                    $result = Financelog::Calculate_Val_advance($changes);
                    $finance_log[$audittrail->id][$changes->field_name] = $result;
                }
            }
            if (!isset($finance_log[$audittrail->id]['cash_flow']) && $action_array[$audittrail->audit_trail_action_name] != 'Delete') {

                $type_2_detail = AuditTrailChanges::where('audit_trail_id', '=', $finance_log[$audittrail->id]['id'])->get();

                foreach ($type_2_detail as $detail) {
                    $result = Financelog::Calculate_Val($detail);
                    $finance_log[$audittrail->id]['detail_log'][$detail->id] = array(
                        'field_name' => $detail->field_name,
                        'field_before' => $result['before'],
                        'field_after' => $result['after']
                    );
                }
            }
        }

        foreach ($finance_log as $key => $log) {
            if ($log['sr no'] == 1 && $log['cash_flow'] == 'out' && $log['payment_mode_id'] == 'Settle Amount') {
                unset($finance_log[$key]);
            }
        }

        if ($type === 'web') {
            return view('admin.packages.log', compact('finance_log', 'id'));
        }

        return $this->packagelogexcel($id, $finance_log);
    }

    /**
     *  Function for log for package
     */
    public function packagelogexcel($id, $finance_log)
    {
        if (!Gate::allows('plans_log')) {
            return abort(401);
        }

        $spreadsheet = new Spreadsheet();
        $Excel_writer = new Xlsx($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'PACKAGE ID')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', $id);


        $activeSheet->setCellValue('A2', '#')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', 'Cash Flow')->getStyle('B2')->getFont()->setBold(true);
        $activeSheet->setCellValue('C2', 'Cash Amount')->getStyle('C2')->getFont()->setBold(true);
        $activeSheet->setCellValue('D2', 'Refund')->getStyle('D2')->getFont()->setBold(true);
        $activeSheet->setCellValue('E2', 'Adjustment')->getStyle('E2')->getFont()->setBold(true);
        $activeSheet->setCellValue('F2', 'Tax')->getStyle('F2')->getFont()->setBold(true);
        $activeSheet->setCellValue('G2', 'Cancel')->getStyle('G2')->getFont()->setBold(true);
        $activeSheet->setCellValue('H2', 'Delete')->getStyle('H2')->getFont()->setBold(true);
        $activeSheet->setCellValue('I2', 'Refund Note')->getStyle('I2')->getFont()->setBold(true);
        $activeSheet->setCellValue('J2', 'Payment Mode')->getStyle('J2')->getFont()->setBold(true);
        $activeSheet->setCellValue('K2', 'Appointment Type')->getStyle('K2')->getFont()->setBold(true);
        $activeSheet->setCellValue('L2', 'Location')->getStyle('L2')->getFont()->setBold(true);
        $activeSheet->setCellValue('M2', 'Created By')->getStyle('M2')->getFont()->setBold(true);
        $activeSheet->setCellValue('N2', 'Updated By')->getStyle('N2')->getFont()->setBold(true);
        $activeSheet->setCellValue('O2', 'Plan')->getStyle('O2')->getFont()->setBold(true);
        $activeSheet->setCellValue('P2', 'Invoice Id')->getStyle('P2')->getFont()->setBold(true);
        $activeSheet->setCellValue('Q2', 'Created At Shown')->getStyle('Q2')->getFont()->setBold(true);
        $activeSheet->setCellValue('R2', 'Updated At Shown')->getStyle('R2')->getFont()->setBold(true);
        $activeSheet->setCellValue('S2', 'Created At')->getStyle('S2')->getFont()->setBold(true);
        $activeSheet->setCellValue('T2', 'Updated At')->getStyle('T2')->getFont()->setBold(true);
        $activeSheet->setCellValue('U2', 'Deleted At')->getStyle('U2')->getFont()->setBold(true);


        $count = 1;
        $counter = 4;

        foreach ($finance_log as $log) {
            if ((isset($log['package_id']) && $log['package_id'] == $id) || !isset($log['package_id'])) {
                $activeSheet->setCellValue('A' . $counter, $count++);
                $activeSheet->setCellValue('B' . $counter, isset($log['cash_flow']) ? $log['cash_flow'] : '-');
                $activeSheet->setCellValue('C' . $counter, isset($log['cash_amount']) ? $log['cash_amount'] : '-');
                $activeSheet->setCellValue('D' . $counter, isset($log['is_refund']) ? $log['is_refund'] : '-');
                $activeSheet->setCellValue('E' . $counter, isset($log['is_adjustment']) ? $log['is_adjustment'] : '-');
                $activeSheet->setCellValue('F' . $counter, isset($log['is_tax']) ? $log['is_tax'] : '-');
                $activeSheet->setCellValue('G' . $counter, isset($log['is_cancel']) ? $log['is_cancel'] : '-');
                $activeSheet->setCellValue('H' . $counter, ($log['action'] == 'Delete') ? 'Yes' : '-');
                $activeSheet->setCellValue('I' . $counter, isset($log['refund_note']) ? $log['refund_note'] : '-');
                $activeSheet->setCellValue('J' . $counter, isset($log['payment_mode_id']) ? $log['payment_mode_id'] : '-');
                $activeSheet->setCellValue('K' . $counter, isset($log['appointment_type_id']) ? $log['appointment_type_id'] : '-');
                $activeSheet->setCellValue('L' . $counter, isset($log['location_id']) ? $log['location_id'] : '-');
                $activeSheet->setCellValue('M' . $counter, isset($log['created_by']) ? $log['created_by'] : '-');
                $activeSheet->setCellValue('N' . $counter, isset($log['cash_flow']) ? isset($log['updated_by']) ? $log['updated_by'] : '-' : $log['user_id']);
                $activeSheet->setCellValue('O' . $counter, isset($log['package_id']) ? $log['package_id'] : '-');
                $activeSheet->setCellValue('P' . $counter, isset($log['invoice_id']) ? $log['invoice_id'] : '-');
                $activeSheet->setCellValue('Q' . $counter, isset($log['created_at']) ? $log['created_at'] == $log['created_at_orignal'] ? '-' : $log['created_at'] : '-');
                $activeSheet->setCellValue('R' . $counter, isset($log['updated_at']) ? $log['updated_at'] == $log['updated_at_orignal'] ? '-' : $log['updated_at'] : '-');

                if ($log['action'] == 'Delete') {
                    $activeSheet->setCellValue('S' . $counter, '-');
                    $activeSheet->setCellValue('T' . $counter, '-');
                } else {
                    $activeSheet->setCellValue('S' . $counter, isset($log['created_at_orignal']) ? \Carbon\Carbon::parse($log['created_at_orignal'])->format('F j,Y h:i A') : '-');
                    $activeSheet->setCellValue('T' . $counter, isset($log['updated_at_orignal']) ? \Carbon\Carbon::parse($log['updated_at_orignal'])->format('F j,Y h:i A') : '-');
                }

                $activeSheet->setCellValue('U' . $counter, isset($log['deleted_at']) ? \Carbon\Carbon::parse($log['deleted_at'])->format('F j, Y h:i A') : '-');

                $counter++;


                if (isset($log['detail_log']) && count($log['detail_log'])) {

                    $countt = 1;

                    $activeSheet->setCellValue('H' . $counter, '#')->getStyle('H' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('I' . $counter, 'Field Name')->getStyle('I' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('J' . $counter, 'Before')->getStyle('J' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('K' . $counter, 'After')->getStyle('K' . $counter)->getFont()->setBold(true);

                    $counter++;

                    foreach ($log['detail_log'] as $detail) {
                        $activeSheet->setCellValue('H' . $counter, $countt++);
                        $activeSheet->setCellValue('I' . $counter, isset($detail['field_name']) ? $detail['field_name'] : '-');
                        $activeSheet->setCellValue('J' . $counter, isset($detail['field_before']) ? $detail['field_before'] : '-');
                        $activeSheet->setCellValue('K' . $counter, isset($detail['field_after']) ? $detail['field_after'] : '-');

                        $counter++;
                    }
                }
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'PackageLog' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /**
     * Load plan Sms History.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showSMSLogs($id)
    {
        $SMSLogs = SMSLogs::where('package_id', '=', $id)->orderBy('created_at', 'desc')->get();

        return view('admin.packages.sms_logs', compact('SMSLogs'));
    }

    /**
     * Re-send Plan SMS
     *
     * @param \App\Http\Requests\Admin\StoreUpdateAppointmentsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function sendLogSMS(Request $request)
    {

        $data = $request->all();

        $SMSLog = SMSLogs::findOrFail($request->get('id'));

        if ($SMSLog) {
            $response = $this->resendSMS($SMSLog->id, $SMSLog->to, $SMSLog->text, $SMSLog->package_id);

            if ($response['status']) {
                return response()->json(['status' => 1]);
            }
        }

        return response()->json(['status' => 0]);
    }

    /**
     * Calling sms log
     *
     * @param \App\Http\Requests\Admin\StoreUpdateAppointmentsRequest $request
     * @return \Illuminate\Http\Response
     */
    private function resendSMS($smsId, $patient_phone, $preparedText, $package_id)
    {
        $package_info = Packages::find($package_id);

        $setting = Settings::whereSlug('sys-current-sms-operator')->first();

        $UserOperatorSettings = UserOperatorSettings::getRecord($package_info->account_id, $setting->data);

        if ($setting->data == 1) {
            $SMSObj = array(
                'username' => $UserOperatorSettings->username, // Setting ID 1 for Username
                'password' => $UserOperatorSettings->password, // Setting ID 2 for Password
                'to' => $patient_phone,
                'text' => $preparedText,
                'mask' => $UserOperatorSettings->mask, // Setting ID 3 for Mask
                'test_mode' => $UserOperatorSettings->test_mode, // Setting ID 3 Test Mode
            );
            $response = TelenorSMSAPI::SendSMS($SMSObj);
        } else {
            $SMSObj = array(
                'username' => $UserOperatorSettings->username, // Setting ID 1 for Username
                'password' => $UserOperatorSettings->password, // Setting ID 2 for Password
                'from' => $UserOperatorSettings->mask,
                'to' => $patient_phone,
                'text' => $preparedText,
                'test_mode' => $UserOperatorSettings->test_mode, // Setting ID 3 Test Mode
            );
            $response = JazzSMSAPI::SendSMS($SMSObj);
        }
        if ($response['status']) {
            SMSLogs::find($smsId)->update(['status' => 1]);
        }

        return $response;
    }

    /**
     * Function get the variable to search in database to get the package
     *
     * */
    public function getpackage(Request $request)
    {
        $package = Packages::where('name', 'LIKE', "%{$request->q}%")->select('name', 'id')->get();
        return response()->json($package);
    }

    /**
     * Function get the variable to search in database to get the package selling
     *
     */
    public function getpackageselling(Request $request)
    {
        $packageselling = PackageSelling::where('id', 'LIKE', "%{$request->q}%")->select('name', 'id')->get();
        return response()->json($packageselling);
    }

    /**
     * Get the discount group
     */
    public function getdiscountgroup(Request $request)
    {

        $discount_info = Discounts::find($request->discount_id);

        if ($discount_info) {
            return response()->json(array(
                'status' => true,
                'group' => $discount_info->slug
            ));
        } else {
            return response()->json(array(
                'status' => false
            ));
        }
    }

    /**
     * Get the month key
     */
    public function getmonthcount($name)
    {
        $month = array(
            '1' => 'January',
            '2' => 'February',
            '3' => 'March',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        );
        return array_search($name, $month);
    }

    /**
     * Get the calculation for periodic discount
     */
    public function getdiscountinfoperiodic(Request $request)
    {
        $discount_allocation = DiscountAllocation::where('id', '=', $request->reference_id)->first();

        $discount_data = Discounts::find($request->discount_id);

        $service_data = Bundles::find($request->service_id);

        $response = $this->getdiscountallocationcalculation($discount_allocation->user_id, $request->random_id, $request->unique_id, $discount_data);

        if ($response['total_remaining_amount'] < $request->discount_value) {
            return response()->json(array(
                'status' => false
            ));
        }
        return response()->json(array(
            'status' => true,
            'net_amount' => $service_data->price - $request->discount_value
        ));
    }

    public function getdiscountallocationcalculation($user_id, $random_id, $unique_id, $discount_data)
    {

        $date = Carbon::now();

        $startOfYear = $date->copy()->startOfYear()->toDateString();
        $endOfYear   = $date->copy()->endOfYear()->toDateString();

        // Total use Amount : periodic_reference_id = present, Package_id = not null
        $periodic_sum_1 = PackageBundles::where('periodic_reference_id', '=', $user_id)->whereNotNull('package_id')->whereDate('created_at', '>=', $startOfYear)->whereDate('created_at', '<=', $endOfYear)->sum('discount_price');

        // Total use Amount : periodic_reference_id = present, package_id = null, random_id = request->random_id
        $periodic_sum_2 = PackageBundles::where([['periodic_reference_id', '=', $user_id], ['random_id', '=', $random_id], ['unique_id', '=', $unique_id]])->whereDate('created_at', '>=', $startOfYear)->whereDate('created_at', '<=', $endOfYear)->sum('discount_price');

        // Total Avail amount
        $per_month_discount = $discount_data->amount / 12;

        $date = new Carbon($discount_data->created_at);

        $monthName = $date->format('F');

        $month_count = $this->getmonthcount($monthName);

        $remaining_month = 12 - ($month_count - 1);

        $total_avail_amount = $remaining_month * $per_month_discount;

        $total_use_amount = $periodic_sum_1 + $periodic_sum_2;

        $total_remaining_amount = $total_avail_amount - $total_use_amount;

        return array('total_avail_amount' => $total_avail_amount, 'total_use_amount' => $total_use_amount, 'total_remaining_amount' => $total_remaining_amount);
    }
}
