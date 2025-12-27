<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\GeneralFunctions;
use App\Helpers\Invoice_Plan_Refund_Sms_Functions;
use App\Helpers\Widgets\AppointmentEditWidget;
use App\Helpers\Widgets\LocationsWidget;
use App\Http\Controllers\Api\App\ApiHelpers\Plan;
use App\Jobs\IndexSingleAppointmentJob;
use App\Models\Appointments;
use App\Models\AppointmentStatuses;
use App\Models\Cities;
use App\Models\Discounts;
use App\Models\InvoiceDetails;
use App\Models\Invoices;
use App\Models\InvoiceStatuses;
use App\Models\Leads;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\Notification;
use App\Models\MachineTypeHasServices;
use App\Models\NotificationLog;
use App\Models\PackageAdvances;
use App\Models\PackageBundles;
use App\Models\Packages;
use App\Models\PackageSellingService;
use App\Models\PackageService;
use App\Models\Patients;
use App\Models\PaymentModes;
use App\Models\ResourceHasRota;
use App\Models\ResourceHasRotaDays;
use App\Models\Resources;
use App\Models\Services;
use App\Models\Settings;
use App\Models\WalletMeta;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\SMSTemplates;
use DB;

class TreatmentController extends Controller
{
    public function getplanagainsttreatment(Request $request) {

        $package_service_id = 0;
        $price = 0;
        $tax = 0;
        $tax_amt = 0;
        // First get the number of plans against patient
        $package = Packages::leftjoin('package_services', 'packages.id', '=', 'package_services.package_id')
            ->where([
                ['packages.is_refund', '=', '0'],
                ['packages.active', '=', '1'],
                ['packages.patient_id', '=', Auth::user()->id],
                ['package_services.service_id', '=', $request->service_id],
                ['package_services.is_consumed', '=', '0'],
                ['packages.location_id', '=', $request->location_id],
                ['packages.is_hold','=','0']
            ])->select('packages.*')->groupby('packages.id')->orderBy('packages.id', 'desc')->first();
        if ($package) {
            // Now get the services which belongs to that package
            $packageservices = PackageService::join('services', 'package_services.service_id', '=', 'services.id')
                ->where([
                    ['package_services.package_id', '=', $package->id],
                    ['package_services.service_id', '=', $request->service_id],
                    ['package_services.is_consumed', '=', 0]
                ])
                ->select('package_services.*', 'services.name as servicename')
                ->get();

            if (count($packageservices) > 0) {
                // Now get the single low tax_including_price value service
                $lowestPrice = PHP_FLOAT_MAX;  // Initialize with highest possible value
                $lowestData = array();
                foreach ($packageservices as $data) {
                    if ($data['tax_including_price'] < $lowestPrice) {
                        $lowestPrice = $data['tax_including_price'];
                        $lowestData = $data;
                        $package_service_id = $data['id'];
                        $price = $data['tax_exclusive_price'];
                        $tax = $data['tax_price'];
                        $tax_amt = $data['tax_including_price'];
                    }
                }
                // Now I need to perform calculation and send plan information
                // Invoice Calculation
                $balance_patient_in = PackageAdvances::where([
                    ['patient_id', '=', Auth::user()->id],
                    ['package_id', '=', $package->id],
                    ['cash_flow', '=', 'in']
                ])->sum('cash_amount');

                $balance_patient_out = PackageAdvances::where([
                    ['patient_id', '=', Auth::user()->id],
                    ['package_id', '=', $package->id],
                    ['cash_flow', '=', 'out']
                ])->sum('cash_amount');

                $balance = $balance_patient_in - $balance_patient_out;

                if ($balance >= $lowestData['tax_including_price']) {
                    $settle_amount = 0;
                } else {
                    $settle_amount = $lowestData['tax_including_price'] - $balance;
                }
                // Plan Information
                $total_price = PackageBundles::where('package_id', '=', $package->id)->sum('tax_including_price');
                $cash_receive = PackageAdvances::where([
                    ['package_id', '=', $package->id],
                    ['cash_flow', '=', 'in'],
                    ['is_cancel', '=', '0']
                ])->sum('cash_amount');
                $records = array(
                    'id' => $package->id,
                    'name' => $package->user->name,
                    'location_id' => $package->location->city->name." - ".$package->location->name,
                    'session_count' => count(PackageBundles::where('package_id', '=', $package->id)->get()),
                    'total' => $total_price,
                    'cash_received' => $cash_receive,
                    'remaining' => $total_price - $cash_receive,
                    'refund' => 'No',
                    'created_at' => Carbon::parse($package->created_at)->format('F j,Y h:i A'),
                    'status' => '',
                    'bundle' => array(),
                    'finance' => Plan::getplanfinancedetail($package->id),
                );
                $data = Plan::getplantreatmentdetail($package->id);

                $records['bundle'] = $data['bundles'];
                $records['status'] = $data['status'];

                return response()->json([
                    'status' => true,
                    'message' => 'Information fetch sucessfully',
                    'plan' => $records,
                    'plan_id' => $package->id,
                    'package_service_id' => $package_service_id,
                    'price' => $price,
                    'tax' => $tax,
                    'tax_amt' => $tax_amt,
                    'settleAmount' => $settle_amount,
                    'status_code' => Response::HTTP_OK,
                ]);
            }  else {
                return response()->json([
                    'status' => false,
                    'message' => 'No Plan found against that treatment, Please visit 3D Center',
                    'status_code' => Response::HTTP_OK,
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No Plan found against that treatment, Please visit 3D Center',
                'status_code' => Response::HTTP_OK,
            ]);
        }
    }

    public function get_doctors_for_treatment(Request $request)
    {
        $practitioner = array();

        $doctors = $doctors_no_final = LocationsWidget::loadAppointmentDoctorByLocation($request->get("location_id"), auth()->user()->account_id);

        foreach ($doctors_no_final as $key => $doctor) {
            $resource = Resources::where('external_id', '=', $key)->first();
            $doctor_rota = ResourceHasRota::where([
                ['resource_id', '=', $resource->id],
                ['active', '=', 1],
                ['is_treatment', '=', 1]
            ])->get();

            if (count($doctor_rota) == 0) {
                unset($doctors[$key]);
            }
        }
        foreach ($doctors as $key => $doctor) {
            $serviceIds = LocationsWidget::loadAppointmentServiceByLocationDoctor($request->get("location_id"), $key, auth()->user()->account_id, true);
            if ((in_array($request->get('service_id'), $serviceIds))) {
                $practitioner[] = [
                    'id' => $key,
                    'name' => $doctor
                ];
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Doctor information successfully given against city, location and service',
            'doctors' => $practitioner,
            'status_code' => Response::HTTP_OK,
        ]);
    }

    /**
     *  Get the dates of doctor rota against location and doctor
     */
    public function get_doctor_rota_dates_for_treatment(Request $request)
    {
        $rotaDays = array();

        $resourceInfo = Resources::where([
            ["external_id", "=", $request->get("doctor_id")],
            ["resource_type_id", "=", Resources::getResourceType("doctor")],
            ["account_id", "=", auth()->user()->account_id]
        ])->first();

        $resourceRotas = ResourceHasRota::where([
            ['location_id', '=', $request->get("location_id")],
            ['is_treatment', '=', 1],
            ['active', '=', 1],
            ['resource_id', '=', $resourceInfo->id]
        ])->get();
        $number_days_allowed = Settings::where('slug', '=', 'sys-number-of-restricted-days-for-treatment')->first();
        if (count($resourceRotas) >= 1) {
            foreach ($resourceRotas as $key => $resourceRota) {
                $rotaDays[$key] = array(
                    'resourceId' => $resourceInfo->id,
                    'resourceRotaId' => $resourceRota->id,
                    'dates' => array()
                );
                $startDate = Carbon::createFromFormat('Y-m-d', $resourceRota->start);
                $endDate = Carbon::createFromFormat('Y-m-d', $resourceRota->end);
                $check = Carbon::now()->between($startDate, $endDate);
                if ($check) {
                    $dates = ResourceHasRotaDays::where([
                        ['resource_has_rota_id', '=', $resourceRota->id],
                        ['active', '=', 1],
                        ['date', '>=', Carbon::now()->format('Y-m-d')]
                    ])
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time')
                        ->limit(30)
                        ->select('id', 'date')
                        ->skip($number_days_allowed->data)
                        ->get();
                    foreach ($dates as $key2 => $date) {
                        $rotaDays[$key]['dates'][$key2] = [
                            'resourceRotaDayId' => $date->id,
                            'date' => $date->date
                        ];
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Doctor Rota information is given',
                    'Rotas' => $rotaDays,
                    'status_code' => Response::HTTP_OK,
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Doctor Rota is not defined',
                'Rotas' => $rotaDays,
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }

    /**
     * @param Request $request
     */
    private function verify_fields_for_get_doctor_rota_dates_and_time_for_treatment(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'location_id' => 'required',
            'service_id' => 'required',
            'resourceRotaDayId' => 'required',
        ]);
    }

    /**
     * get doctor rota dates and time for treatment
     * @param Request $request ( doctor_id, service_id,resource_rota_day_id, location_id )
     *
     * */
    public function get_doctor_rota_dates_and_time_for_treatment(Request $request)
    {

        $validation = $this->verify_fields_for_get_doctor_rota_dates_and_time_for_treatment($request);

        if ($validation->fails()) {
            return response([
                'status' => false,
                'message' => $validation->messages()->all(),
                'status_code' => 422
            ]);
        }

        // get the date using resource has rota day id
        $resourcehasrotaday = ResourceHasRotaDays::query()
            ->where('id', $request->resourceRotaDayId)
            ->where('active', 1)
            ->first();

        $date = $resourcehasrotaday->date;

        $newDate = Carbon::parse($resourcehasrotaday->date);

        if ($newDate->gte(Carbon::now()->format('Y-m-d'))) {

        } else {
            return \response([
                'status' => false,
                'message' => 'You cannot book an appointment on back date',
                'status_code' => 422
            ]);
        }

        // fetching the doctor
        /*$doctor = Resources::where('external_id', $request->doctor_id)
            ->where('resource_type_id', Resources::getResourceType("doctor"))
            ->where('account_id', auth()->user()->account_id)
            ->first();*/

        // fetching the service
        $parentService = $service = Services::where('active', 1)->find($request->service_id);

        if ($service->parent_id !== 0) {
            // get last parent service of a service
            $parentService = $this->getLastParentService($request->service_id);
        }

        // getting the time duration of the service
        $duration = explode(":", $service->duration);


        // getting the machine types id against the service
        $machineTypeForServiceId = MachineTypeHasServices::query()
            ->where('service_id', $parentService->id)
            ->get()
            ->pluck('machine_type_id')
            ->toArray();


        // fetching the machines on the basis of location and machine types
        $machinesIds = Resources::query()
            ->where('active', 1)
            ->where('resource_type_id', Resources::getResourceType("machine"))
            ->where('location_id', $request->location_id)
            ->whereIn('machine_type_id', $machineTypeForServiceId)
            ->where('account_id', auth()->user()->account_id)
            ->get()
            ->pluck('id')
            ->toArray();


        // fetching the appointments count on the basis of the doctor and machines related to the service
        $appointments = Appointments::query()
            ->where('active', 1)
            ->where('appointment_type_id', \config('constants.appointment_type_service'))
            ->where('doctor_id', $request->doctor_id)
            ->whereDate('scheduled_date', $date)
            ->whereIn('resource_id', $machinesIds)
            ->select(\DB::raw('COUNT(*) as number_of_appointments'), 'resource_id')
            ->groupBy(['resource_id'])
            ->get()
            ->toArray();

        if (count($appointments))
            $resourceId = min($appointments)['resource_id'];
        else
            $resourceId = $machinesIds[0];

        // get the resource rota for machine
        $resourceHasRota = ResourceHasRota::query()
            ->where('location_id', $request->location_id)
            ->where('resource_type_id', Resources::getResourceType("machine"))
            ->where('active', 1)
            ->where('resource_id', $resourceId)
            ->where('is_treatment', 1)
            ->whereDate('start', '<=', $date)
            ->whereDate('end', '>=', $date)
            ->first();

        // get the resource rota days for machine
        $resourceHasRotaDayForMachine = ResourceHasRotaDays::query()
            ->where('resource_has_rota_id', $resourceHasRota->id)
            ->whereDate('date', $date)
            ->where('active', 1)
            ->first();

        // Union taking of times
        $start_doctor = Carbon::parse($resourcehasrotaday->start_time);
        $end_doctor = Carbon::parse($resourcehasrotaday->end_time);

        $start_machine = Carbon::parse($resourceHasRotaDayForMachine->start_time);
        $end_machine = Carbon::parse($resourceHasRotaDayForMachine->end_time);

        $startTime = '';
        $endTime = '';

        if ($start_doctor->gte($start_machine)) {
            $startTime = $start_doctor;
        } else {
            $startTime = $start_machine;
        }

        if ($end_doctor->lte($end_machine)) {
            $endTime = $end_doctor;
        } else {
            $endTime = $end_machine;
        }

        // query to check if future appointments are related to machine
        $doctor_id = $request->doctor_id;

        // we Also ignore the cancelled appointments
        $default_cancelled_status = AppointmentStatuses::where('is_cancelled', '=', '1')->first();
        if ($default_cancelled_status) {
            $default_cancelled_status = $default_cancelled_status->id;
        } else {
            $default_cancelled_status = 0;
        }
        /*$appointments = Appointments::query()
            ->where('active', 1)
            ->where('appointment_type_id', \config('constants.appointment_type_service'))
            ->whereDate('scheduled_date', $date)
            ->whereTime('scheduled_time', '>=', $startTime->format('H:i:s'))
            ->whereTime('scheduled_time', '<=', $endTime->format('H:i:s'))
            ->where(function ($query) use ($doctor_id, $resourceId) {
                $query->where('doctor_id', $doctor_id)->where('resource_id', $resourceId);
                $query->orWhere('resource_id', $resourceId);
            })
            ->select('scheduled_date', 'scheduled_time')
            ->orderBy('scheduled_time')
            ->get();*/
        // lets suppose if consult also perform as operator that s why I comment above code and write new one
        $appointments = Appointments::query()
            ->where('active', 1)
            ->where('base_appointment_status_id', '!=', $default_cancelled_status)
            ->whereDate('scheduled_date', $date)
            ->whereTime('scheduled_time', '>=', $startTime->format('H:i:s'))
            ->whereTime('scheduled_time', '<=', $endTime->format('H:i:s'))
            ->where(function ($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            })
            ->select('scheduled_date', 'scheduled_time')
            ->orderBy('scheduled_time')
            ->get();

        // making slots for the booking the appointment / treatment
        $time = array();

        if ($resourcehasrotaday->start_off) {

            $startTime = Carbon::parse($resourcehasrotaday->start_time);
            $startTimeOff = Carbon::parse($resourcehasrotaday->start_off);

            while ($startTime->lte($startTimeOff)) {
                $to = $startTime->copy()->addHour($duration[0])->addMinute($duration[1]);

                array_push($time, $startTime->toTimeString());
                $startTime = $to;
            }

            array_pop($time);

            $endTimeOff = Carbon::parse($resourcehasrotaday->end_off);
            $endTime = Carbon::parse($resourcehasrotaday->end_time);
            $endTime = $endTime->subHour($duration[0])->subMinute($duration[1]);

            while ($endTimeOff->lte($endTime)) {
                $to = $endTimeOff->copy()->addHour($duration[0])->addMinute($duration[1]);
                array_push($time, $endTimeOff->toTimeString());
                $endTimeOff = $to;
            }

        } else {

            $startTime = Carbon::parse($startTime);
            $endTime = Carbon::parse($endTime);

            $endTime = $endTime->subHour($duration[0])->subMinute($duration[1]);
            while ($startTime->lte($endTime)) {
                $to = $startTime->copy()->addHour($duration[0])->addMinute($duration[1]);
                array_push($time, $startTime->toTimeString());
                $startTime = $to;
            }
        }

        $data = array();

        // excluding times because of appointments
        if ($appointments->count()) {
            $appointments = $appointments->toArray();
            foreach ($time as $key => $timeTo) {
                for ($i = 0; $i < count($appointments); $i++) {
                    $appointment_time = Carbon::parse($appointments[$i]['scheduled_time']);
                    $timeTo = Carbon::parse($timeTo);
                    $nextTime = '';
                    if (isset($time[$key + 1])) {
                        $nextTime = Carbon::parse($time[$key + 1]);
                        //if ($appointment_time->eq($timeTo) || $appointment_time->between($timeTo, $nextTime)) {
                        // I change it let s see it work fine or not
                        if ($appointment_time->eq($timeTo) || ($appointment_time > $timeTo && $appointment_time < $nextTime)) {
                            unset($time[$key]);
                        }
                    }
                }
            }
        }


        $data['doctor_id'] = ( int )$request->doctor_id;
        $data['doctor_rota_day_id'] = $resourcehasrotaday->id;
        $data['resource_id'] = $resourceId;
        $data['resource_rota_day_id'] = $resourceHasRotaDayForMachine->id;
        $data['date'] = $date;
        $data['RotaTime'] = array_values($time);


        return response()->json([
            'status' => true,
            'message' => 'Doctor Timing information is given',
            'data' => $data,
            'status_code' => 200,
        ]);

    }

    private function getLastParentService($service_id)
    {
        $service = Services::query()
            ->where('active', 1)
            ->find($service_id);

        while ($service->parent_id !== 0) {
            $service = Services::query()
                ->where('active', 1)
                ->find($service->parent_id);
        }

        return $service;
    }

    /**
     * Verifying fields for save treatment
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    private function verify_fields_for_save_treatment(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'doctor_rota_day_id' => 'required',
            'resource_id' => 'required',
            'resource_rota_day_id' => 'required',
            'location_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'service_id' => 'required',
            'plan_id' => 'required',
            'package_service_id' => 'required',
            'price' => 'required',
            'tax' => 'required',
            'tax_amt' => 'required',
            'settleAmount' => 'required',
            'wallet' => 'required',
        ]);
    }

    /**
     * Saving the treatment appointment
     *
     * @param Request $request ( doctor_id, doctor_rota_day_id, resource_id, resource_rota_day_id)
     *
     * */
    public function save_treatment_final(Request $request)
    {
        try {
            $validation = $this->verify_fields_for_save_treatment($request);

            if ($validation->fails()) {
                return response()->json(array(
                    'status' => false,
                    'message' => $validation->messages()->all(),
                    'status_code' => 422
                ));
            }

            // get the user
            $user = $request->user();

            // check here, is number of allow appointment already there
            $appointment_status = AppointmentStatuses::getADefaultStatusOnly(Auth::User()->account_id);
            $number_appointment_allowed = Settings::where('slug', '=', 'sys-number-of-treatment-allow')->first();
            $number_of_appointment = Appointments::where([
                ['patient_id', '=', $user->id],
                ['base_appointment_status_id', '=', $appointment_status->id],
                ['appointment_type_id', '=', '2']
            ])
                ->where(DB::raw("CONCAT(scheduled_date, ' ', scheduled_time)"), '>=', Carbon::now()->toDateTimeString())
                ->count();
            if ($number_of_appointment >= $number_appointment_allowed->data) {
                return response()->json(array(
                    'status' => false,
                    'message' => $number_appointment_allowed->data . ' number of appointments in row, you already exceed limit',
                    'status_code' => 422
                ));
            }

            $doctor_id = $request->doctor_id;
            $resource_id = $request->resource_id;

            $appointments = Appointments::query()
                ->whereTime('scheduled_time', $request->time)
                ->whereDate('scheduled_date', $request->date)
                ->where('location_id', $request->location_id)
                ->where(function ($query) use ($doctor_id, $resource_id) {
                    $query->where('doctor_id', $doctor_id)->where('doctor_id', $doctor_id);
                    $query->orWhere('resource_id', $resource_id);
                })
                ->where('appointment_type_id', \config('constants.appointment_type_service'))
                ->exists();

            if ($appointments) {
                return \response([
                    'status' => false,
                    'message' => 'Doctor is busy please select any other time',
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY
                ]);
            }

            // Here First I need to manage plan and wallet transaction

            $package = Packages::whereActive(1)->wherePatient_id($request->user()->id)->find($request->plan_id);

            if ( !$package ){
                return response([
                    'status' => false,
                    'message' => 'Plan not found !',
                    'status_code' => 404,
                ]);
            }

            if ($request->wallet == 'true' && $request->settleAmount == 0) {
                return response([
                    'success' => false,
                    'message' => 'No need of wallet, Plan already have amount',
                    'status_code' => 422,
                ]);
            }

            if ($request->wallet == 'false' && $request->settleAmount > 0) {
                return response([
                    'success' => false,
                    'message' => 'Please enable wallet, Plan not have sufficient balance',
                    'status_code' => 422,
                ]);
            }

            \Illuminate\Support\Facades\DB::beginTransaction();

            if ($request->wallet == 'true') {

                $wallet_in = WalletMeta::where([
                    ['cash_flow','=','in'],
                    ['cash_amount','>',0],
                    ['wallet_id','=',$request->user()->wallet->id]
                ])->sum('cash_amount');

                $wallet_out = WalletMeta::where([
                    ['cash_flow','=','out'],
                    ['cash_amount','>',0],
                    ['wallet_id','=',$request->user()->wallet->id]
                ])->sum('cash_amount');

                $walletbalance = $wallet_in - $wallet_out;

                if($walletbalance < $request->settleAmount){
                    return response([
                        'success' => false,
                        'message' => 'Your wallet balance is insufficient',
                        'status_code' => 422,
                    ]);
                }

                $data_packageAdvances['cash_flow'] = 'in';
                $data_packageAdvances['cash_amount'] = $request->settleAmount;
                $data_packageAdvances['patient_id'] = $package->patient_id;
                $data_packageAdvances['payment_mode_id'] = null;
                $data_packageAdvances['created_by'] = Auth::User()->id;
                $data_packageAdvances['updated_by'] = Auth::User()->id;
                $data_packageAdvances['package_id'] = $package->id;
                $data_packageAdvances['location_id'] = $package->location_id;
                $data_packageAdvances['wallet_id'] = $request->user()->wallet->id;
                $data_packageAdvances['account_id'] = 1;

                $packageAdavances = PackageAdvances::createRecord($data_packageAdvances, $package);

                if ($packageAdavances && isset($packageAdavances)) {
                    $record = array(
                        'cash_flow' => 'out',
                        'cash_amount' => $request->settleAmount,
                        'wallet_id' => $request->user()->wallet->id,
                        'patient_id' => $request->user()->id,
                        'payment_mode_id' => 5,
                        'account_id' => 1
                    );
                    WalletMeta::create($record);
                }
            }
            // End

            // Find if lead exist or not
            $loadleadData = $this->loadleadData($request, $user);

            $appointmentData = $request->all();
            $appointmentData['account_id'] = $user->account_id;
            $appointmentData['phone'] = GeneralFunctions::cleanNumber($user->phone);
            $appointmentData['created_by'] = $user->id;
            $appointmentData['updated_by'] = $user->id;
            $appointmentData['converted_by'] = $user->id;
            $appointmentData['doctor_id'] = $request->doctor_id;
            $appointmentData['resource_has_rota_day_id'] = $request->doctor_rota_day_id;
            $appointmentData['resource_id'] = $request->resource_id;
            $appointmentData['resource_has_rota_day_id_for_machine'] = $request->resource_rota_day_id;
            $appointmentData['name'] = $user->name;
            $appointmentData['consultancy_type'] = 'treatment';
            $appointmentData['source'] = 'MOBILE';

            // Get Location object to retrieve City
            $location = Locations::with('city', 'region')->findOrFail($appointmentData['location_id']);

            // Set City ID after retrieving from Location
            $appointmentData['city_id'] = $location->city->id;
            $appointmentData['region_id'] = $location->region->id;


            // Set default appointment status i.e. 'pending'
            $appointment_status = AppointmentStatuses::getADefaultStatusOnly($user->account_id);
            if ($appointment_status) {
                $appointmentData['appointment_status_id'] = $appointment_status->id;
                $appointmentData['base_appointment_status_id'] = $appointment_status->id;
                $appointmentData['appointment_status_allow_message'] = $appointment_status->allow_message;
            } else {
                $appointmentData['appointment_status_id'] = null;
                $appointmentData['base_appointment_status_id'] = null;
                $appointmentData['appointment_status_allow_message'] = 0;
            }

            // Set Appointment Type
            $appointmentData['appointment_type_id'] = Config::get('constants.appointment_type_service');

//        dd( $appointmentData );

            /*
             * Check if Lead ID not provided then create a new lead
             * and assign this lead to current appointment.
             */

            if (!$loadleadData['lead_id']) {

                $appointmentData['scheduled_date'] = Carbon::parse($request->get("date"))->format("Y-m-d");
                $appointmentData['scheduled_time'] = Carbon::parse($request->get("time"))->format("H:i:s");

                $appointmentData['first_scheduled_date'] = Carbon::parse($request->get("date"))->format("Y-m-d");
                $appointmentData['first_scheduled_time'] = Carbon::parse($request->get("time"))->format("H:i:s");
                $appointmentData['first_scheduled_count'] = 1;

                $leadObj = $appointmentData;
                $leadObj['patient_id'] = $user->id;
                // Convert Lead status to Converted
                $DefaultConvertedLeadStatus = LeadStatuses::where(array(
                    'account_id' => $user->account_id,
                    'is_converted' => 1,
                ))->first();
                if ($DefaultConvertedLeadStatus) {
                    $default_converted_lead_status_id = $DefaultConvertedLeadStatus->id;
                } else {
                    $default_converted_lead_status_id = Config::get('constants.lead_status_converted');
                }
                $leadObj['lead_status_id'] = $default_converted_lead_status_id;
                $leadObj['lead_source_id'] = $user->lead_source_id;
                $leadObj['source'] = 'MOBILE';

                $lead = Leads::createRecord($leadObj, $user, $status = "Appointment");
            } else {
                $appointmentData['scheduled_date'] = Carbon::parse($request->get("date"))->format("Y-m-d");
                $appointmentData['scheduled_time'] = Carbon::parse($request->get("time"))->format("H:i:s");

                $appointmentData['first_scheduled_date'] = Carbon::parse($request->get("date"))->format("Y-m-d");
                $appointmentData['first_scheduled_time'] = Carbon::parse($request->get("time"))->format("H:i:s");


                $appointmentData['first_scheduled_count'] = 1;

                $lead = Leads::findOrFail($loadleadData['lead_id']);
            }


            // Set Lead ID for Appointment
            $appointmentData['patient_id'] = $user->id;
            $appointmentData['lead_id'] = $lead->id;
            /*
             * End Lead ID Process
             */

            $appointmentData['created_at'] = Carbon::parse(Carbon::now())->toDateTimeString();
            $appointmentData['updated_at'] = Carbon::parse(Carbon::now())->toDateTimeString();
            $appointmentData['updated_by'] = $user->id;

            $appointment = Appointments::create($appointmentData);
            // $appointmentId = $appointment->id;
            /* Now We need to update name of all appointments that already in appointment table against patient
             */
            Appointments::where('patient_id', '=', $appointmentData['patient_id'])->update(['name' => $appointmentData['name']]);


            // If Lead ID provided then change it's status to converted
            if ($loadleadData['lead_id']) {
                $lead = Leads::findOrFail($loadleadData['lead_id']);
                if ($lead) {
                    // Convert Lead status to Converted
                    $DefaultConvertedLeadStatus = LeadStatuses::where(array(
                        'account_id' => $user->account_id,
                        'is_converted' => 1,
                    ))->first();
                    if ($DefaultConvertedLeadStatus) {
                        $default_converted_lead_status_id = $DefaultConvertedLeadStatus->id;
                    } else {
                        $default_converted_lead_status_id = Config::get('constants.lead_status_converted');
                    }
                    $data = array(
                        'lead_status_id' => $default_converted_lead_status_id
                    );
                    $lead = Leads::updateRecord($lead->id, $data, $lead, $status = "Appointment");
                }
            }

            // Update Treatment ID as well
            if ($loadleadData['lead_id']) {
                $lead = Leads::findOrFail($loadleadData['lead_id']);

                if ($lead) {
                    $lead->update(['service_id' => $request->get('service_id')]);
                }
            }

            // Based on allow message by status and scheduled date, allow send sms
            if ($appointment->appointment_status_allow_message && $appointment->scheduled_date) {
                $appointment->update(array(
                    'send_message' => 1
                ));
            }

            /*
             * Set Appointment Status if appointment scheduled date & time are not defined
             * case 1: If Scheduled Date is not set then status is 'un-scheduled'
             * case 2: If 'un-scheduled' is not set then set defautl status i.e. 'pending'
             */
            if (!$appointment->scheduled_date && !$appointment->scheduled_time) {
                $appointment_status = AppointmentStatuses::getUnScheduledStatusOnly($user->account_id);
                if ($appointment_status) {
                    $appointment->update(array(
                        'appointment_status_id' => $appointment_status->id,
                        'base_appointment_status_id' => $appointment_status->id,
                        'appointment_status_allow_message' => 0
                    ));
                } else {
                    // Set default appointment status i.e. 'pending'
                    $appointment_status = AppointmentStatuses::getADefaultStatusOnly($user->account_id);
                    if ($appointment_status) {
                        $appointment->update(array(
                            'appointment_status_id' => $appointment_status->id,
                            'base_appointment_status_id' => $appointment_status->id,
                            'appointment_status_allow_message' => 0
                        ));
                    } else {
                        $appointment->update(array(
                            'appointment_status_id' => null,
                            'base_appointment_status_id' => null,
                            'appointment_status_allow_message' => 0
                        ));
                    }
                }
            }

            // Now times to ring up the invoice

            $invoicestatus = InvoiceStatuses::where('slug', '=', 'paid')->first();
            $paymentmode_settle = PaymentModes::where('payment_type', '=', Config::get('constants.payment_type_settle'))->first();

            $package_service_info = PackageService::where([
                ['package_id', '=', $request->plan_id],
                ['id', '=', $request->package_service_id]
            ])->first();

            $is_exclusive = $package_service_info->is_exclusive;

            // invoice create
            $data['total_price'] = $request->tax_amt;
            $data['account_id'] = Auth::User()->account_id;
            $data['patient_id'] = $appointment->patient_id;
            $data['appointment_id'] = $appointment->id;
            $data['invoice_status_id'] = $invoicestatus->id;
            $data['created_by'] = Auth::User()->id;
            $data['location_id'] = $appointment->location_id;
            $data['doctor_id'] = $appointment->doctor_id;
            $data['is_exclusive'] = $is_exclusive;

            $invoice = Invoices::CreateRecord($data);

            // invoice detail create
            $data_detail['tax_exclusive_serviceprice'] = $request->price;
            $data_detail['tax_percenatage'] = $appointment->location->tax_percentage;
            $data_detail['tax_price'] = $request->tax;
            $data_detail['tax_including_price'] = $request->tax_amt;
            $data_detail['net_amount'] = $request->tax_amt;
            $data_detail['is_exclusive'] = $is_exclusive;

            $data_detail['qty'] = '1';
            $data_detail['service_price'] = $appointment->service->price;
            $data_detail['service_id'] = $appointment->service_id;
            $data_detail['invoice_id'] = $invoice->id;

            $data_detail['tax_percenatage'] = $package_service_info->tax_percenatage;
            $data_detail['package_service_id'] = $request->package_service_id;

            $packages = DB::table('packages')
                ->join('package_bundles', 'packages.id', '=', 'package_bundles.package_id')
                ->join('package_services', 'package_bundles.id', '=', 'package_services.package_bundle_id')
                ->where([
                    ['packages.id', '=', $request->plan_id],
                    ['package_services.service_id', '=', $appointment->service_id]
                ])->select('package_bundles.discount_type', 'package_bundles.discount_price', 'package_bundles.discount_id')->first();

            if ($packages->discount_type != null) {
                $discount_info = Discounts::find($packages->discount_id);
                $data_detail['discount_type'] = $packages->discount_type;
                $data_detail['discount_price'] = $packages->discount_price;
                $data_detail['discount_id'] = $packages->discount_id;
                $data_detail['discount_name'] = $discount_info->name;
            }
            $data_detail['package_id'] = $request->plan_id;

            $invoice_detail = InvoiceDetails::createRecord($data_detail, $invoice);

            $out_transcation_price = $request->tax_amt - $invoice_detail->tax_price;
            $out_transcation_tax = $invoice_detail->tax_price;

            $tran = array(
                '1' => $out_transcation_price,
                '2' => $out_transcation_tax
            );

            $count = 0;

            foreach ($tran as $trans) {
                if ($count == '1') {
                    $data_package['is_tax'] = 1;
                }
                $data_package['cash_flow'] = 'out';
                $data_package['cash_amount'] = $trans;
                $data_package['patient_id'] = $appointment->patient_id;
                $data_package['payment_mode_id'] = $paymentmode_settle->id;
                $data_package['account_id'] = Auth::User()->account_id;
                $data_package['appointment_type_id'] = $appointment->appointment_type_id;
                $data_package['appointment_id'] = $appointment->id;
                $data_package['location_id'] = $appointment->location_id;
                $data_package['invoice_id'] = $invoice->id;
                $data_package['created_by'] = Auth::User()->id;
                $data_package['updated_by'] = Auth::User()->id;
                $data_package['package_id'] = $invoice_detail->package_id;
                PackageAdvances::createRecord_forinvoice($data_package);
                $count++;
            }

            PackageService::where('id', '=', $request->package_service_id)->update(['is_consumed' => 1]);
            $packagesservice = PackageService::find($request->package_service_id);
            PackageService::updateRecordInvoice($packagesservice);

            Invoice_Plan_Refund_Sms_Functions::InvoiceCashReceived_SMS($invoice, $invoice_detail, $request->plan_id);

            /*
             * Dispatch Elastic Search Index
             */
            // Before un comment kindly test it
//            $this->dispatch(
//                new IndexSingleAppointmentJob([
//                    'account_id' => Auth::User()->account_id,
//                    'appointment_id' => $appointment->id
//                ])
//            );

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Treatment Booked successfully !',
                'status_code' => 200,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Some issue Occurred! Please try again !',
                'status_code' => 422,
            ]);
        }
    }

    /**
     *  Function to get the notification against given user
     */
    public function getnotifications($user_id)
    {
        return response([
            'status' => true,
            'message' => 'Data fetch successfully',
            'data' => NotificationLog::getNotifications($user_id),
            'status_code' => 200
        ]);
    }

    /**
     *  Function to update the is_read column in notification logs table
     */
    public function updatenotification(Request $request)
    {
        $notification = NotificationLog::find($request->id);

        if ($notification->is_read == 0) {
            $notification->update(['is_read' => 1]);
        }

        return response([
            'status' => true,
            'message' => 'Data update successfully',
            'status_code' => 200
        ]);
    }

    /**
     * Get the Lead Source
     */
    public static function loadLeadData($request, $userinfo)
    {
        $data = array(
            'status' => 0,
            'patient_id' => 0,
            'phone' => null,
            'cnic' => null,
            'gender' => null,
            'dob' => null,
            'address' => null,
            'town_id' => null,
            'referred_by' => null,
            'name' => null,
            'email' => null,
            'service_id' => null,
            'lead_source_id' => null,
        );

        $phone = GeneralFunctions::cleanNumber($userinfo->phone);

        $patient = Patients::getByPhone($phone, $userinfo->account_id, $userinfo->id);

        $lead = Leads::where(['patient_id' => $patient->id, 'service_id' => $request->get('service_id')])->first();

        if ($lead) {
            $data['service_id'] = $lead->service_id;
            $data['lead_source_id'] = $lead->lead_source_id;
            $data['lead_id'] = $lead->id;
            $data['town_id'] = $lead->town_id;
        } else {
            $data['service_id'] = $request->get('service_id');
            $data['lead_id'] = '';
        }

        $data['patient_id'] = $patient->id;
        $data['phone'] = $patient->phone;
        $data['dob'] = $patient->dob;
        $data['address'] = $patient->address;
        $data['cnic'] = $patient->cnic;
        $data['referred_by'] = $patient->referred_by;
        $data['name'] = $patient->name;
        $data['email'] = $patient->email;
        $data['gender'] = $patient->gender;

        return $data;
    }

    /**
     * Verify fields for update treatment appointment
     * */
    private function verify_fields_for_edit_treatment(Request $request)
    {
        return Validator::make($request->all(), [
            'appointment_id' => 'required',
            'doctor_id' => 'required',
            'doctor_rota_day_id' => 'required',
            'resource_id' => 'required',
            'resource_rota_day_id' => 'required',
            'location_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'service_id' => 'required',
        ]);
    }

    /**
     * Update Treatment Appointment
     */
    public function update_treatment_appointment(Request $request)
    {

        $validator = $this->verify_fields_for_edit_treatment($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
                'id' => 0,
            ));
        }

        // Get the user info
        $userinfo = User::find(Auth::user()->id);

        $appointment = Appointments::findOrFail($request->appointment_id);
        $value_of_sending_message = $appointment->send_message;

        $location_info = Locations::findOrFail($request->location_id);

        $city_info = Cities::find($location_info->city_id);

        $appointmentData = $request->all();
        $appointmentData['region_id'] = $city_info->region_id;
        $appointmentData['city_id'] = $city_info->id;
        $appointmentData['phone'] = GeneralFunctions::cleanNumber($userinfo->phone);
        $appointmentData['updated_by'] = Auth::user()->id;

        $appointmentData['updated_at'] = Carbon::parse(Carbon::now())->toDateTimeString();
        $appointmentData['updated_by'] = Auth::User()->id;
        $appointmentData['scheduled_date'] = Carbon::parse($appointmentData['date'])->format("Y-m-d");
        $appointmentData['scheduled_time'] = Carbon::parse($appointmentData['time'])->format("H:i:s");

        // Reset Scheduled Time to null, stop sending message
        $appointment_status = AppointmentStatuses::getADefaultStatusOnly(Auth::User()->account_id);
        if ($appointment_status) {
            $appointmentData['appointment_status_id'] = $appointment_status->id;
            $appointmentData['base_appointment_status_id'] = $appointment_status->id;
            $appointmentData['appointment_status_allow_message'] = $appointment_status->allow_message;
            $appointmentData['send_message'] = $appointment_status->allow_message;
        }
        /*
        * Grab Rota day info and update
        */
        $appointmentData['resource_id'] = $appointmentData['resource_id'];
        $appointmentData['resource_has_rota_day_id'] = $appointmentData['doctor_rota_day_id'];
        $appointmentData['resource_has_rota_day_id_for_machine'] = $appointmentData['resource_rota_day_id'];

        $appointment->update($appointmentData);


        if (count($appointment->getChanges()) > 1) {
            // if only doctor are going to change and first sms already sent, so we need to stop sending message again
            if ($value_of_sending_message == '0') {
                $changes = $appointment->getChanges();
                // in future if edit form increase input field so we need to change that count also
                // And Reader I didnt find any proper way so I use static check
                if ($appointment->appointment_type_id == Config::get('constants.appointment_type_service')) {
                    if (count($changes) == 4) {
                        if (isset($changes['doctor_id'])) {
                            $appointment->update(['send_message' => 0]);
                        }
                    } else if (count($changes) == 2) {
                        $appointment->update(['send_message' => $value_of_sending_message]);
                    }
                } else {
                    if (count($changes) == 5) {
                        if (isset($changes['doctor_id'])) {
                            $appointment->update(['send_message' => 0]);
                        }
                    } else if (count($changes) == 2) {
                        $appointment->update(['send_message' => $value_of_sending_message]);
                    }
                }

            }
            // End: That code only belong to stop sending message
            $scheduled_at_count = $appointment->scheduled_at_count;
            $appointment->update(['scheduled_at_count' => $scheduled_at_count + 1]);
        }


        /*
         * Perform Lead Operations
         */
        $lead = Leads::findOrFail($appointment->lead_id);
        $lead->update($appointmentData);
        $patient = Patients::findOrFail($lead->patient_id);
        $patientData = $appointmentData;
        $patient = Patients::updateRecord($lead->patient_id, $patientData);

        /*
         * Lead Operations End
         */

        /**
         * Dispatch Elastic Search Index
         */
//        $this->dispatch(
//            new IndexSingleAppointmentJob([
//                'account_id' => Auth::User()->account_id,
//                'appointment_id' => $appointment->id
//            ])
//        );

        return response()->json(array(
            'status' => true,
            'message' => 'Appointment has been updated successfully.',
            'status_code' => 200,
        ));

    }

}
