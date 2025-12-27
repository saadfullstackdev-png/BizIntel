<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\Payments\Meezan;
use App\Http\Controllers\Api\App\ApiHelpers\Plan;
use App\Models\Accounts;
use App\Models\BundleHasServices;
use App\Models\Bundles;
use App\Http\Controllers\Controller;
use App\Models\Discounts;
use App\Models\Locations;
use App\Models\PackageAdvances;
use App\Models\PackageBundles;
use App\Models\Packages;
use App\Models\PackageSelling;
use App\Models\PackageSellingService;
use App\Models\PackageService;
use App\Models\PaymentModes;
use App\Models\Services;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\WalletMeta;
use App\Models\PurchasedService;
use Auth;
use App\Http\Controllers\Api\App\ApiHelpers\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Config;
use App\Http\Controllers\Api\App\ApiHelpers\Apivalidation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;


class PackageController extends Controller
{
    /*
     * That function give package information to sell
     */
    public function getpackages()
    {
        $records = array();
        $packages = Bundles::where([['active', '=', '1'],['is_mobile', '!=', 3]])->orderby('created_at', 'DESC')->get();
        if (count($packages) > 0) {
            foreach ($packages as $key => $package) {
                $records[$key] = array(
                    'id' => $package->id,
                    'name' => $package->name,
                    'offered_price' => $package->price,
                    'services_price' => $package->services_price,
                    'total_services' => $package->total_services,
                    'description' => $package->description,
                    'image_src' => $package->image_src ? '/bundle_images/' . $package->image_src : '/default_image/ae877a93f4983269a8c9520ad011a46f.png',
                    'treatment' => Package::getpackagereatmentdetail($package)
                );
            }
            return response([
                'success' => true,
                'message' => "Package Data",
                'data' => $records
            ], 200);
        } else {
            return response([
                'success' => false,
                'message' => "Package data not exist",
            ], 404);
        }
    }

    public function getpackagedetail($id)
    {
        $records = array();
        $packages = Bundles::where([['active', '=', '1'],['is_mobile', '!=', 3],['id','=',$id]])->orderby('created_at', 'DESC')->get();
        if (count($packages) > 0) {
            foreach ($packages as $key => $package) {
                $records[$key] = array(
                    'id' => $package->id,
                    'name' => $package->name,
                    'offered_price' => $package->price,
                    'services_price' => $package->services_price,
                    'total_services' => $package->total_services,
                    'description' => $package->description,
                    'image_src' => $package->image_src ? '/bundle_images/' . $package->image_src : '/default_image/ae877a93f4983269a8c9520ad011a46f.png',
                    'treatment' => Package::getpackagereatmentdetail($package)
                );
            }
            return response([
                'success' => true,
                'message' => "Package Data",
                'data' => $records
            ], 200);
        } else {
            return response([
                'success' => false,
                'message' => "Package data not exist",
            ], 404);
        }
    }

    /*
     * Get the locations for package selling
     */
    public function getCentres()
    {
        $records = Locations::where([
            ['active', '=', 1],
            ['slug', '=', 'custom']
        ])->select('id', 'name')->get();
        if (!$records->isEmpty()) {
            return response([
                'success' => true,
                'message' => "Center Data List",
                'data' => $records
            ], 200);
        } else {
            return response([
                'success' => false,
                'message' => "Data Not Found!",
                'data' => $records
            ], 400);
        }
    }

    /*
     * Get the package calculation
     */
    public function getpackagecalculation(Request $request)
    {

        $location = Locations::where('id', '=', $request->location_id)->first();

        $package = Bundles::where('id', '=', $request->package_id)->first();

        $calculation = Package::packagetaxcal($package->price, $location->tax_percentage, $package->tax_treatment_type_id);

        return response([
            'success' => true,
            'message' => "Package Selling calculation",
            'data' => $calculation
        ], 200);
    }

    /*
     * Save the Package Sell
     */
    public function savepackagesell(Request $request)
    {
        $validator = Apivalidation::packagesellingvalidation($request);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->messages()->all(),
                'status_code' => 422,
            ]);
        }

        $transaction = '';

        if ($request->wallet == 'false') {

            $transaction = Transaction::where('user_id', $request->user()->id)->find($request->transaction_id);

            $orderStatus = Meezan::updateOrderStatus($transaction->order_id, $transaction);

            if (!$orderStatus['status']) {
                return response([
                    'status' => false,
                    'message' => 'Transaction was not made successfully',
                    'data' => null,
                    'status_code' => 422,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

        } else {

            $wallet_in = WalletMeta::where([
                ['cash_flow', '=', 'in'],
                ['cash_amount', '>', 0],
                ['wallet_id', '=', $request->user()->wallet->id]
            ])->sum('cash_amount');

            $wallet_out = WalletMeta::where([
                ['cash_flow', '=', 'out'],
                ['cash_amount', '>', 0],
                ['wallet_id', '=', $request->user()->wallet->id]
            ])->sum('cash_amount');

            $walletbalance = $wallet_in - $wallet_out;

            if ($walletbalance < $request->amount) {
                return response([
                    'success' => false,
                    'message' => 'Your wallet balance is insufficient',
                    'status_code' => 422,
                ]);
            }
        }

        DB::beginTransaction();

        try {
            $package = Bundles::where('id', '=', $request->package_id)->first();
            $location = Locations::where('id', '=', $request->location_id)->first();

            $data = array();

            $data['bundle_id'] = $request->package_id;
            $data['patient_id'] = Auth::User()->id;
            $data['location_id'] = $request->location_id;
            $data['name'] = $package->name;
            $data['actual_price'] = $package->services_price;
            $data['offered_price'] = $package->price;
            $data['total_services'] = $package->total_services;
            $data['apply_discount'] = $package->apply_discount;

            $calculation = Package::packagetaxcal($package->price, $location->tax_percentage, $package->tax_treatment_type_id);

            $data['is_exclusive'] = $calculation['is_exclusive'];
            $data['tax_exclusive_price'] = $calculation['price'];
            $data['tax_percentage'] = $location->tax_percentage;
            $data['tax_price'] = $calculation['tax'];
            $data['tax_including_price'] = $calculation['tax_amt'];

            $package_selling = PackageSelling::create($data);

            if ($package_selling) {

                $bundle_details = BundleHasServices::where('bundle_id', '=', $request->package_id)->get();
                $data = array();

                foreach ($bundle_details as $detail) {

                    $calculation = Package::packagetaxcal($detail->calculated_price, $location->tax_percentage, $package->tax_treatment_type_id);
                    $service = Services::find($detail->service_id);
                    $data[] = array(
                        'package_selling_id' => $package_selling->id,
                        'bundle_id' => $request->package_id,
                        'service_id' => $detail->service_id,
                        'autual_price' => $service->price,
                        'offered_price' => $detail->calculated_price,
                        'is_exclusive' => $calculation['is_exclusive'],
                        'tax_exclusive_price' => $calculation['price'],
                        'tax_percentage' => $location->tax_percentage,
                        'tax_price' => $calculation['tax'],
                        'tax_including_price' => $calculation['tax_amt']
                    );
                }
                $package_selling_service = PackageSellingService::insert($data);

                if ($package_selling_service) {

                    Plan::saveplan($package_selling, $location, $request, $transaction);

                    DB::commit();

                    return response([
                        'status' => true,
                        'message' => 'Package Purchase Successfully',
                        'status_code' => 200,
                    ]);
                } else {
                    return response([
                        'status' => false,
                        'message' => 'Invalid Request',
                        'status_code' => 422,
                    ]);
                }
            } else {
                return response([
                    'status' => false,
                    'message' => 'Invalid Request',
                    'status_code' => 422,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => false,
                'message' => 'Some issue Occurred! Please try again !',
                'status_code' => 422,
            ]);
        }
    }

    public function purchased_services(Request $request)
    {

        $validator = Apivalidation::purchasedservicesvalidation($request);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->messages()->all(),
                'status_code' => 422,
            ]);
        }

        // Calculate wallet balance
        $wallet_id = $request->user()->wallet->id;
        $wallet_in = WalletMeta::where([
            ['cash_flow', '=', 'in'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $wallet_id]
        ])->sum('cash_amount');

        $wallet_out = WalletMeta::where([
            ['cash_flow', '=', 'out'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $wallet_id]
        ])->sum('cash_amount');

        $wallet_balance = $wallet_in - $wallet_out;

        // Check if wallet balance is sufficient
        if ($wallet_balance < $request->price) {
            return response()->json([
                'success'     => false,
                'message'     => 'Your wallet balance is insufficient',
                'status_code' => 422,
            ], 422);
        }

        // Process transaction
        try {
            DB::beginTransaction();
            $data_packageAdvances['cash_flow'] = 'in';
            $data_packageAdvances['cash_amount'] = $request->price;
            $data_packageAdvances['patient_id'] = $request->patient_id;
            $data_packageAdvances['payment_mode_id'] = null;
            $data_packageAdvances['created_by'] = Auth::User()->id;
            $data_packageAdvances['updated_by'] = Auth::User()->id;
            $data_packageAdvances['package_id'] = null;
            $data_packageAdvances['location_id'] = $request->location_id;
            $data_packageAdvances['wallet_id'] = $wallet_id;
            
            $data_packageAdvances['account_id'] = 1;
            /*End*/

            PackageAdvances::create($data_packageAdvances);
            // Insert purchased service
            $purchasedService = PurchasedService::create([
                'patient_id'  => $request->patient_id,
                'location_id' => $request->location_id,
                'service_id'  => $request->service_id,
                'price'       => $request->price,
                'is_consumed' => 0,
            ]);

            // Deduct from wallet
            WalletMeta::create([
                'wallet_id'   => $wallet_id,
                'patient_id'  => $request->patient_id,
                'cash_flow'   => 'out',
                'cash_amount' => $request->price,
                'description' => 'Service Purchase',
            ]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Service purchased successfully',
                'data'        => $purchasedService,
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success'     => false,
                'message'     => 'Transaction failed: ' . $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }
    public function patient_Purchasedservices(Request $request)
    {
        $purchasedService = PurchasedService::leftjoin('services','services.id','=','purchased_services.service_id')
                ->leftjoin('locations','locations.id','=','purchased_services.location_id')
                ->leftjoin('users','users.id','=','purchased_services.patient_id')->select('services.name as service_name','locations.name as location_name','users.name as patient_name','purchased_services.price as purchased_services_price','purchased_services.is_consumed','purchased_services.created_at','users.phone as patient_phone')
                ->where('patient_id', '=', Auth::User()->id)
                ->get();
        return response([
            'success' => true,
            'data' => $purchasedService
        ], 200);
    }
    /*
     * Get the selled packaged against user
     */
    public function getselledpackage()
    {

        $records = array();

        $packageSellings = PackageSelling::where('patient_id', '=', Auth::User()->id)->orderby('created_at', 'DESC')->get();

        if ($packageSellings) {
            foreach ($packageSellings as $key => $packageSelling) {
                $bundle_info = Bundles::where('id', '=', $packageSelling->bundle_id)->first();
                $plan_info = Packages::where('package_selling_id', '=', $packageSelling->id)->first();
                $records[$key] = array(
                    'id' => $packageSelling->id,
                    'location_id' => $packageSelling->location->city->name." - ".$packageSelling->location->name,
                    'image_src' => $bundle_info->image_src ? '/bundle_images/' . $bundle_info->image_src : '/default_image/ae877a93f4983269a8c9520ad011a46f.png',
                    'name' => $packageSelling->name,
                    'total_services' => $packageSelling->total_services,
                    'is_refund' => $packageSelling->is_refund ? 'Yes' : 'No',
                    'tax_including_price' => number_format($packageSelling->tax_including_price, 2),
                    'status' => '',
                    'pdf' =>  '/getselledpackage/pdf/'.$plan_info->id,
                    'packageSellingServices' => array()
                );
                $data = Package::getpackageSellingService($packageSelling->id);
                $records[$key]['packageSellingServices'] = $data['records'];
                $records[$key]['status'] = $data['status'];
            }
            return response([
                'success' => true,
                'message' => "Package selling data",
                'data' => $records
            ], 200);
        } else {
            return response([
                'success' => false,
                'message' => "Package selling data not exist",
            ], 404);
        }
    }

    /**
     * Print the package.
     */
    public function get_package_pdf($id)
    {
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

        $content = view('admin.packagesellings.pdf', compact('package', 'packagebundles', 'packageservices', 'packageadvances', 'services', 'discount', 'paymentmodes', 'grand_total', 'location_info', 'account_info', 'company_phone_number'));
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($content);
        return $pdf->stream('Package Invoice');
    }
}
