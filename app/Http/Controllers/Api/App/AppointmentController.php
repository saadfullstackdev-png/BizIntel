<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\ACL;
use App\Helpers\GeneralFunctions;
use App\Helpers\Widgets\AppointmentCheckesWidget;
use App\Helpers\Widgets\LocationsWidget;
use App\Http\Resources\CityResource;
use App\Http\Resources\LocationResource;
use App\Jobs\IndexSingleAppointmentJob;
use App\Models\Accounts;
use App\Models\Appointments;
use App\Models\AppointmentStatuses;
use App\Models\Cities;
use App\Models\Discounts;
use App\Models\Invoices;
use App\Models\InvoiceStatuses;
use App\Models\Leads;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\Patients;
use App\InvoiceScanLog;
use App\Models\ResourceHasRota;
use App\Models\ResourceHasRotaDays;
use App\Models\Resources;
use App\Models\Services;
use App\Models\Settings;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Models\AppointmentTypes;
use DB;
use Illuminate\Support\Facades\App;

class AppointmentController extends Controller
{
    /**
     * Get the consultancy type
     */
    public function getconsultancytype()
    {

        $setting = Settings::where('slug', '=', 'sys-virtual-consultancy')->first();

        $type = array(
            [
                'id' => 'in_person',
                'name' => 'In Person'
            ],
            [
                'id' => 'virtual',
                'name' => 'Virtual'
            ]
        );

        return response()->json([
            'status' => true,
            'golobal_virtual_appointment_setting' => $setting->data == 1 ? true : false,
            'types' => $type,
            'status_code' => 200,
        ]);

    }

    /**
     * Get Cities in consultancy booking
     */
    public function getCities()
    {
        $cities = Cities::where('active', 1)->where('active', 1)->where('is_featured', 1)->where('account_id', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Cities information successfully given',
            'cities' => CityResource::collection($cities),
            'status_code' => 200,
        ]);
    }

    /**
     * Get centres against city id for consultancy
     */
    public function getCentres(Request $request)
    {
        $locations = Locations::where([
            ['city_id', $request->city_id],
            ['slug', '=', 'custom']
        ])->where('active', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Locations information successfully given against city',
            'centres' => LocationResource::collection($locations),
            'status_code' => 200,
        ]);
    }

    /**
     * Get Doctors against each centre
     */
    public function getDoctors(Request $request)
    {
        $practitioner = array();

        $doctors = $doctors_no_final = LocationsWidget::loadAppointmentDoctorByLocation($request->get("location_id"), Auth::User()->account_id);

        foreach ($doctors_no_final as $key => $doctor) {
            $resource = Resources::where('external_id', '=', $key)->first();
            $doctor_rota = ResourceHasRota::where([
                ['resource_id', '=', $resource->id],
                ['is_consultancy', '=', '1']
            ])->get();
            if (count($doctor_rota) == 0) {
                unset($doctors[$key]);
            }
        }
        foreach ($doctors as $key => $doctor) {
            $serviceIds = LocationsWidget::loadAppointmentServiceByLocationDoctor($request->get("location_id"), $key, Auth::User()->account_id);
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
            'status_code' => 200,
        ]);
    }

    /**
     *  Get the dates of doctor rota against location and doctor
     */
    public function getdoctorrotadates(Request $request)
    {
        $rotaDays = array();

        $resourceInfo = Resources::where([
            ["external_id", "=", $request->get("doctor_id")],
            ["resource_type_id", "=", Resources::getResourceType("doctor")],
            ["account_id", "=", Auth::User()->account_id]
        ])->first();

        $resourceRotas = ResourceHasRota::where([
            ['location_id', '=', $request->get("location_id")],
            ['is_consultancy', '=', '1'],
            ['active', '=', '1'],
            ['resource_id', '=', $resourceInfo->id]
        ])->get();
        $number_days_allowed = Settings::where('slug', '=', 'sys-number-of-restricted-days-for-consultancy')->first();
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
                        ['active', '=', '1'],
                        ['date', '>=', Carbon::now()->format('Y-m-d')]
                    ])
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time')
                        ->limit(30)
                        ->select('id', 'date', 'start_time', 'end_time')
                        ->skip($number_days_allowed->data)
                        ->get();

                    $default_cancelled_status = AppointmentStatuses::where('is_cancelled', '=', '1')->first();
                    if ($default_cancelled_status) {
                        $default_cancelled_status = $default_cancelled_status->id;
                    } else {
                        $default_cancelled_status = 0;
                    }

                    foreach ($dates as $key2 => $date) {

                        //Here we check booked appointment count between start and end of the day
                        $start_time = Carbon::createFromFormat('g:i A', $date->start_time)->format('H:i:s');
                        $end_time = Carbon::createFromFormat('g:i A', $date->end_time)->format('H:i:s');

                        $booked_appointment = Appointments::where(function ($query) use ($date, $start_time, $end_time) {
                            $query->whereDate('scheduled_date', '>=', $date->date)
                                ->whereTime('scheduled_time', '>=', $start_time)
                                ->whereDate('scheduled_date', '<=', $date->date)
                                ->whereTime('scheduled_time', '<=', $end_time);
                        })
                            ->where('base_appointment_status_id', '!=', $default_cancelled_status)
                            ->where('resource_has_rota_day_id', '=', $date->id)
                            ->count();

                        $rotaDays[$key]['dates'][$key2] = [
                            'resourceRotaDayId' => $date->id,
                            'date' => $date->date,
                            'count' => $booked_appointment
                        ];
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Doctor Rota information is given',
                    'Rotas' => $rotaDays,
                    'status_code' => 200,
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Doctor Rota is not defined',
                'Rotas' => $rotaDays,
                'status_code' => 500,
            ]);
        }
    }

    /**
     * Get the Time interval of doctor rota
     */
    public function getdoctorrotadatesTime(Request $request)
    {
        $serviceInfo = Services::find($request->service_id);
        $duration = explode(":", $serviceInfo->duration);

        $resourcehasrotaday = ResourceHasRotaDays::where([
            ['id', '=', $request->resourceRotaDayId],
            ['active', '=', '1']
        ])->first();

        $default_cancelled_status = AppointmentStatuses::where('is_cancelled', '=', '1')->first();
        if ($default_cancelled_status) {
            $default_cancelled_status = $default_cancelled_status->id;
        } else {
            $default_cancelled_status = 0;
        }

        $time = array();

        if ($resourcehasrotaday->start_off) {

            $startTime = Carbon::parse($resourcehasrotaday->date . ' ' . $resourcehasrotaday->start_time);
            $startTimeOff = Carbon::parse($resourcehasrotaday->date . ' ' . $resourcehasrotaday->start_off);

            while ($startTime->lte($startTimeOff)) {
                $to = $startTime->copy()->addMinute($duration[0])->addMinute($duration[1]);
                $booked_appointment = Appointments::where(function ($query) use ($startTime, $to) {
                    $query->whereDate('scheduled_date', '>=', $startTime->toDateString())
                        ->whereTime('scheduled_time', '>=', $startTime->toTimeString())
                        ->whereDate('scheduled_date', '<=', $to->toDateString())
                        ->whereTime('scheduled_time', '<', $to->toTimeString());
                })
                    ->where('base_appointment_status_id', '!=', $default_cancelled_status)
                    ->where('resource_has_rota_day_id', '=', $resourcehasrotaday->id)
                    ->count();
                array_push($time, array('time' => $startTime->toTimeString(), 'count' => $booked_appointment));
                $startTime = $to;
            }
            array_pop($time);

            $endTimeOff = Carbon::parse($resourcehasrotaday->date . ' ' . $resourcehasrotaday->end_off);
            $endTime = Carbon::parse($resourcehasrotaday->date . ' ' . $resourcehasrotaday->end_time);

            while ($endTimeOff->lte($endTime)) {
                $to = $endTimeOff->copy()->addMinute($duration[0])->addMinute($duration[1]);
                $booked_appointment = Appointments::where(function ($query) use ($endTimeOff, $to) {
                    $query->whereDate('scheduled_date', '>=', $endTimeOff->toDateString())
                        ->whereTime('scheduled_time', '>=', $endTimeOff->toTimeString())
                        ->whereDate('scheduled_date', '<=', $to->toDateString())
                        ->whereTime('scheduled_time', '<', $to->toTimeString());
                })
                    ->where('base_appointment_status_id', '!=', $default_cancelled_status)
                    ->where('resource_has_rota_day_id', '=', $resourcehasrotaday->id)
                    ->count();
                array_push($time, array('time' => $endTimeOff->toTimeString(), 'count' => $booked_appointment));
                $endTimeOff = $to;
            }
            array_pop($time);
        } else {
            $startTime = Carbon::parse($resourcehasrotaday->date . ' ' . $resourcehasrotaday->start_time);
            $endTime = Carbon::parse($resourcehasrotaday->date . ' ' . $resourcehasrotaday->end_time);
            while ($startTime->lte($endTime)) {
                $to = $startTime->copy()->addHour($duration[0])->addMinute($duration[1]);
                $booked_appointment = Appointments::where(function ($query) use ($startTime, $to) {
                    $query->whereDate('scheduled_date', '>=', $startTime->toDateString())
                        ->whereTime('scheduled_time', '>=', $startTime->toTimeString())
                        ->whereDate('scheduled_date', '<=', $to->toDateString())
                        ->whereTime('scheduled_time', '<', $to->toTimeString());
                })
                    ->where('base_appointment_status_id', '!=', $default_cancelled_status)
                    ->where('resource_has_rota_day_id', '=', $resourcehasrotaday->id)
                    ->count();
                array_push($time, array('time' => $startTime->toTimeString(), 'count' => $booked_appointment));
                $startTime = $to;
            }
            array_pop($time);
        }

        return response()->json([
            'status' => true,
            'message' => 'Doctor Timing information is given',
            'RotaTime' => $time,
            'status_code' => 200,
        ]);
    }

    /**
     * Save Consultancy
     */
    public function saveconsultancy(Request $request)
    {
        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => false,
                'message' => $validator->messages()->all(),
                'status_code' => 422
            ));
        }
        // check here, is number of allow appointment already there
        $appointment_status = AppointmentStatuses::getADefaultStatusOnly(Auth::User()->account_id);
        $number_appointment_allowed = Settings::where('slug', '=', 'sys-number-of-consultancy-allow')->first();
        $number_of_appointment = Appointments::where([
            ['patient_id', '=', $request->user_id],
            ['base_appointment_status_id', '=', $appointment_status->id],
            ['appointment_type_id', '=', '1']
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
        // Check consultancy type
        $service_inforamtion = Services::find($request->service_id);

        if ($request->consultancy_type == 'in_person' || $request->consultancy_type == 'virtual') {

            // Get the user info
            $userinfo = User::find(Auth::user()->id);

            // Find if lead exist or not
            $loadleadData = $this->loadleadData($request, $userinfo);

            $appointmentData = $request->all();
            $appointmentData['account_id'] = Auth::user()->account_id;
            $appointmentData['phone'] = GeneralFunctions::cleanNumber($userinfo->phone);
            $appointmentData['created_by'] = Auth::user()->id;
            $appointmentData['updated_by'] = Auth::user()->id;
            $appointmentData['converted_by'] = Auth::user()->id;
            $appointmentData['resource_id'] = $request->resourceId;
            $appointmentData['resource_has_rota_day_id'] = $request->resourceRotaDayId;
            $appointmentData['name'] = $userinfo->name;
            $appointmentData['source'] = 'MOBILE';

            // Get Location object to retrieve City
            $location = Locations::findOrFail($appointmentData['location_id']);

            // Set City ID after retrieving from Location
            $appointmentData['city_id'] = $request->city_id;
            $appointmentData['region_id'] = $location->region_id;

            // Set default appointment status i.e. 'pending'
            $appointment_status = AppointmentStatuses::getADefaultStatusOnly(Auth::User()->account_id);

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
            $appointmentData['appointment_type_id'] = Config::get('constants.appointment_type_consultancy');

            $appointmentData['account_id'] = Auth::user()->account_id;

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
                $leadObj['patient_id'] = $userinfo->id;
                // Convert Lead status to Converted
                $DefaultConvertedLeadStatus = LeadStatuses::where(array(
                    'account_id' => Auth::user()->account_id,
                    'is_converted' => 1,
                ))->first();
                if ($DefaultConvertedLeadStatus) {
                    $default_converted_lead_status_id = $DefaultConvertedLeadStatus->id;
                } else {
                    $default_converted_lead_status_id = Config::get('constants.lead_status_converted');
                }
                $leadObj['lead_status_id'] = $default_converted_lead_status_id;
                $leadObj['lead_source_id'] = $userinfo->lead_source_id;
                $leadObj['source'] = 'MOBILE';

                $lead = Leads::createRecord($leadObj, $userinfo, $status = "Appointment");
            } else {
                $appointmentData['scheduled_date'] = Carbon::parse($request->get("date"))->format("Y-m-d");
                $appointmentData['scheduled_time'] = Carbon::parse($request->get("time"))->format("H:i:s");

                $appointmentData['first_scheduled_date'] = Carbon::parse($request->get("date"))->format("Y-m-d");
                $appointmentData['first_scheduled_time'] = Carbon::parse($request->get("time"))->format("H:i:s");
                $appointmentData['first_scheduled_count'] = 1;

                $lead = Leads::findOrFail($loadleadData['lead_id']);
            }
            // Set Lead ID for Appointment
            $appointmentData['patient_id'] = $userinfo->id;
            $appointmentData['lead_id'] = $lead->id;
            /*
             * End Lead ID Process
             */
            $appointmentData['created_at'] = Carbon::parse(Carbon::now())->toDateTimeString();
            $appointmentData['updated_at'] = Carbon::parse(Carbon::now())->toDateTimeString();
            $appointmentData['updated_by'] = Auth::User()->id;

            $appointment = Appointments::create($appointmentData);

            /* Now We need to update name of all appointments that already in appointment table against patient
             */
            Appointments::where('patient_id', '=', $appointmentData['patient_id'])->update(['name' => $appointmentData['name']]);

            // If Lead ID provided then change it's status to converted
            if ($loadleadData['lead_id']) {
                $lead = Leads::findOrFail($loadleadData['lead_id']);
                if ($lead) {
                    // Convert Lead status to Converted
                    $DefaultConvertedLeadStatus = LeadStatuses::where(array(
                        'account_id' => Auth::user()->account_id,
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
                $appointment_status = AppointmentStatuses::getUnScheduledStatusOnly(Auth::User()->account_id);
                if ($appointment_status) {
                    $appointment->update(array(
                        'appointment_status_id' => $appointment_status->id,
                        'base_appointment_status_id' => $appointment_status->id,
                        'appointment_status_allow_message' => 0
                    ));
                } else {
                    // Set default appointment status i.e. 'pending'
                    $appointment_status = AppointmentStatuses::getADefaultStatusOnly(Auth::User()->account_id);
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
            /*
             * Dispatch Elastic Search Index
             */
//            $this->dispatch(
//                new IndexSingleAppointmentJob([
//                    'account_id' => session('account_id'),
//                    'appointment_id' => $appointment->id
//                ])
//            );

            return response()->json([
                'status' => true,
                'message' => 'Appointment created successfully',
                'status_code' => 200,
            ]);
        } else {
            return response()->json(array(
                'status' => false,
                'message' => 'Only In Person and Virtual Consultancy Allow',
                'status_code' => 422
            ));
        }
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

        $patient = Patients::getByPhone($phone, Auth::User()->account_id, $userinfo->id);

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
     * Edit consultancy
     */
    public function editconsultancy(Request $request)
    {

        $validator = $this->verifyUpdateFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => false,
                'message' => $validator->messages()->all(),
            ));
        }

        // Check consultancy type
        $service_inforamtion = Services::find($request->service_id);

        if ($request->consultancy_type == 'in_person' || $request->consultancy_type == 'virtual') {

            // Get the user info
            $userinfo = User::find(Auth::user()->id);

            $appointment = Appointments::findOrFail($request->appointment_id);

            $value_of_sending_message = $appointment->send_message;

            $city_info = Cities::find($request->city_id);

            $appointmentData = $request->all();
            $appointmentData['region_id'] = $city_info->region_id;
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

            $appointmentData['resource_id'] = $appointmentData['resourceId'];
            $appointmentData['resource_has_rota_day_id'] = $appointmentData['resourceRotaDayId'];


            $appointment->update($appointmentData);

            if (count($appointment->getChanges()) > 1) {
                // if only doctor are going to change and first sms already sent, so we need to stop sending message again
                if ($value_of_sending_message == '0') {
                    $changes = $appointment->getChanges();
                    // in future if edit form increase input field so we need to change that count also
                    // And Reader I didnt find any proper way so I use static check
                    if (count($changes) == 5) {
                        if (isset($changes['doctor_id'])) {
                            $appointment->update(['send_message' => 0]);
                        }
                    } else if (count($changes) == 2) {
                        $appointment->update(['send_message' => $value_of_sending_message]);
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
//            $this->dispatch(
//                new IndexSingleAppointmentJob([
//                    'account_id' => Auth::User()->account_id,
//                    'appointment_id' => $appointment->id
//                ])
//            );

            return response()->json([
                'status' => true,
                'message' => 'Appointment updated successfully',
                'status_code' => 200,
            ]);
        } else {
            return response()->json(array(
                'status' => false,
                'message' => 'Only In Person and Virtual Consultancy Allow',
                'status_code' => 422
            ));
        }
    }

    /**
     * get appointment consultancy against user id or phone number
     */
    public function getappointments()
    {
        $appointments = array(
            'history' => array(),
            'appointment' => array(),
        );

        $userInfo = User::find(Auth::user()->id);

        $invoice_status = InvoiceStatuses::where('slug', '=', 'paid')->first();

        $setting = Settings::where('slug', '=', 'sys-headoffice')->first();

        $AppointmentStatuses = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);

        $resultQuery = Appointments::join('users', function ($join) {
            $join->on('users.id', '=', 'appointments.patient_id')
                ->where('users.user_type_id', '=', config('constants.patient_id'));
        })
            ->where([
                ['users.id', '=', $userInfo->id],
                ['appointments.appointment_type_id', '=', 1]
            ])
            ->select('*', 'appointments.name as patient_name', 'appointments.id as app_id', 'appointments.created_by as app_created_by', 'appointments.created_at as app_created_at')
            ->orderBy('appointments.created_at', 'desc')
            ->get();

        $historycount = 0;
        $appointmenytcount = 0;

        if (count($resultQuery)) {
            foreach ($resultQuery as $key => $appointment) {
                if ($appointment->consultancy_type == 'virtual') {
                    $virtual_link = $appointment->doctor->virtual_link ? $appointment->doctor->virtual_link : '';
                } else {
                    $virtual_link = '';
                }
                $invoice = Invoices::join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->where([
                        ['invoices.appointment_id', '=', $appointment->app_id],
                        ['invoices.invoice_status_id', '=', $invoice_status->id]
                    ])->first();

                $resourceRotaDayId = ResourceHasRotaDays::find($appointment->resource_has_rota_day_id);
                if ($invoice) {
                    $appointments['history'][$historycount++] = array(
                        'id' => $appointment->app_id,
                        'schedule_date_time' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_date, null)->format('M j, Y') . ' at ' . Carbon::parse($appointment->scheduled_time, null)->format('h:i A') : '-',
                        'schedule_date' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_date, null)->format('M j, Y') : '-',
                        'schedule_time' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_time, null)->format('h:i A') : '-',
                        'date' => $appointment->scheduled_date,
                        'time' => $appointment->scheduled_time,
                        'doctor_id' => $appointment->doctor_id,

                        'doctor_rota_day_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_has_rota_day_id,
                        'resource_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_id,
                        'resource_rota_day_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_has_rota_day_id_for_machine,


                        'resourceId' => $appointment->resource_id,
                        'resourceRotaId' => $resourceRotaDayId->resource_has_rota_id,
                        'resourceRotaDayId' => $appointment->resource_has_rota_day_id,


                        'service_id' => $appointment->service_id ? $appointment->service_id : 'N/A',
                        'service_name' => $appointment->service_id ? $appointment->service->name : 'N/A',
                        'city_id' => $appointment->city_id ? $appointment->city_id : 'N/A',
                        'city_name' => $appointment->city_id ? $appointment->city->name : 'N/A',
                        'location_id' => $appointment->location_id ? $appointment->location_id : 'N/A',
                        'location_name' => $appointment->location_id ? $appointment->location->name : 'N/A',
                        'location_fdo_phone' => $appointment->location_id ? $appointment->location->fdo_phone : 'N/A',
                        'doctor_name' => $appointment->doctor->name,
                        'image_src' => '/service_images/' . $appointment->service->image_src,
                        'head_office_number' => $setting->data,
                        'appointment_status' => ($appointment->appointment_status_id ? ($appointment->appointment_status->parent_id ? $AppointmentStatuses[$appointment->appointment_status->parent_id]->name : $appointment->appointment_status->name) : ''),
                        'invoice_price' => $invoice->tax_including_price,
                        'source' => $appointment->source,
                        'consultancy_type' => $appointment->consultancy_type,
                        'virtual_link' => $virtual_link,
                        'type' => $appointment->appointment_type_id,
                        'pdf' =>  '/getappointmentinvoice/pdf/'.$invoice->invoice_id,
                    );
                } else {
                    $appointments['appointment'][$appointmenytcount++] = array(
                        'id' => $appointment->app_id,
                        'schedule_date_time' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_date, null)->format('M j, Y') . ' at ' . Carbon::parse($appointment->scheduled_time, null)->format('h:i A') : '-',
                        'schedule_date' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_date, null)->format('M j, Y') : '-',
                        'schedule_time' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_time, null)->format('h:i A') : '-',
                        'date' => $appointment->scheduled_date,
                        'time' => $appointment->scheduled_time,
                        'doctor_id' => $appointment->doctor_id,


                        'doctor_rota_day_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_has_rota_day_id,
                        'resource_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_id,
                        'resource_rota_day_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_has_rota_day_id_for_machine,


                        'resourceId' => $appointment->resource_id,
                        'resourceRotaId' => $resourceRotaDayId->resource_has_rota_id,
                        'resourceRotaDayId' => $appointment->resource_has_rota_day_id,

                        'service_id' => $appointment->service_id ? $appointment->service_id : 'N/A',
                        'service_name' => $appointment->service_id ? $appointment->service->name : 'N/A',
                        'city_id' => $appointment->city_id ? $appointment->city_id : 'N/A',
                        'city_name' => $appointment->city_id ? $appointment->city->name : 'N/A',
                        'location_id' => $appointment->location_id ? $appointment->location_id : 'N/A',
                        'location_name' => $appointment->location_id ? $appointment->location->name : 'N/A',
                        'location_fdo_phone' => $appointment->location_id ? $appointment->location->fdo_phone : 'N/A',
                        'doctor_name' => $appointment->doctor->name,
                        'image_src' => '/service_images/' . $appointment->service->image_src,
                        'head_office_number' => $setting->data,
                        'appointment_status' => ($appointment->appointment_status_id ? ($appointment->appointment_status->parent_id ? $AppointmentStatuses[$appointment->appointment_status->parent_id]->name : $appointment->appointment_status->name) : ''),
                        'source' => $appointment->source,
                        'consultancy_type' => $appointment->consultancy_type,
                        'virtual_link' => $virtual_link,
                        'type' => $appointment->appointment_type_id,
                    );
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Get appointment information regarding specific patient',
                'appointment' => $appointments,
                'status_code' => 200,
            ]);

        } else {
            return response()->json([
                'status' => false,
                'message' => 'Record not present!',
                'appointment' => $appointments,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * get appointment treatment against user id or phone number
     */
    public function gettreatmentappointments()
    {
        $appointments = array();

        $userInfo = User::find(Auth::user()->id);

        $invoice_status = InvoiceStatuses::where('slug', '=', 'paid')->first();

        $setting = Settings::where('slug', '=', 'sys-headoffice')->first();

        $AppointmentStatuses = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);

        $resultQuery = Appointments::join('users', function ($join) {
            $join->on('users.id', '=', 'appointments.patient_id')
                ->where('users.user_type_id', '=', config('constants.patient_id'));
        })
            ->where([
                ['users.id', '=', $userInfo->id],
                ['appointments.appointment_type_id', '=', 2]
            ])
            ->select('*', 'appointments.name as patient_name', 'appointments.id as app_id', 'appointments.created_by as app_created_by', 'appointments.created_at as app_created_at')
            ->orderBy('appointments.created_at', 'desc')
            ->get();

        if (count($resultQuery)) {
            foreach ($resultQuery as $key => $appointment) {
                if (Carbon::now()->toDateString() < $appointment->scheduled_date) {
                    $is_editable = true;
                } else {
                    $is_editable = false;
                }
                $invoice = Invoices::join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->where([
                        ['invoices.appointment_id', '=', $appointment->app_id],
                        ['invoices.invoice_status_id', '=', $invoice_status->id]
                    ])->first();

                $resourceRotaDayId = ResourceHasRotaDays::find($appointment->resource_has_rota_day_id);

                $appointments[] = array(
                    'id' => $appointment->app_id,
                    'schedule_date_time' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_date, null)->format('M j, Y') . ' at ' . Carbon::parse($appointment->scheduled_time, null)->format('h:i A') : '-',
                    'schedule_date' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_date, null)->format('M j, Y') : '-',
                    'schedule_time' => ($appointment->scheduled_date) ? Carbon::parse($appointment->scheduled_time, null)->format('h:i A') : '-',
                    'date' => $appointment->scheduled_date,
                    'time' => $appointment->scheduled_time,
                    'doctor_id' => $appointment->doctor_id,

                    'doctor_rota_day_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_has_rota_day_id,
                    'resource_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_id,
                    'resource_rota_day_id' => $appointment->appointment_type_id === 1 ? null : $appointment->resource_has_rota_day_id_for_machine,


                    'resourceId' => $appointment->resource_id,
                    'resourceRotaId' => $resourceRotaDayId->resource_has_rota_id,
                    'resourceRotaDayId' => $appointment->resource_has_rota_day_id,


                    'service_id' => $appointment->service_id ? $appointment->service_id : 'N/A',
                    'service_name' => $appointment->service_id ? $appointment->service->name : 'N/A',
                    'city_id' => $appointment->city_id ? $appointment->city_id : 'N/A',
                    'city_name' => $appointment->city_id ? $appointment->city->name : 'N/A',
                    'location_id' => $appointment->location_id ? $appointment->location_id : 'N/A',
                    'location_name' => $appointment->location_id ? $appointment->location->name : 'N/A',
                    'location_fdo_phone' => $appointment->location_id ? $appointment->location->fdo_phone : 'N/A',
                    'doctor_name' => $appointment->doctor->name,
                    'image_src' => '/service_images/' . $appointment->service->image_src,
                    'head_office_number' => $setting->data,
                    'appointment_status' => ($appointment->appointment_status_id ? ($appointment->appointment_status->parent_id ? $AppointmentStatuses[$appointment->appointment_status->parent_id]->name : $appointment->appointment_status->name) : ''),
                    'invoice_price' => $invoice ? $invoice->tax_including_price : '',
                    'is_invoice' =>  $invoice ? true : false,
                    'source' => $appointment->source,
                    'consultancy_type' => $appointment->consultancy_type,
                    'type' => $appointment->appointment_type_id,
                    'is_editable' => $is_editable,
                    'pdf' =>  $invoice ? '/getappointmentinvoice/pdf/'.$invoice->invoice_id : '',
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Get appointment information regarding specific patient',
                'appointment' => $appointments,
                'status_code' => 200,
            ]);

        } else {
            return response()->json([
                'status' => false,
                'message' => 'Record not present!',
                'appointment' => $appointments,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Verify field before submitt form
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'service_id' => 'required',
            'city_id' => 'required',
            'location_id' => 'required',
            'doctor_id' => 'required',
            'resourceId' => 'required',
            'resourceRotaId' => 'required',
            'resourceRotaDayId' => 'required',
            'date' => 'required',
            'time' => 'required',
            'consultancy_type' => 'required',
        ]);
    }

    /**
     * Validate form fields edit
     */
    protected function verifyUpdateFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'appointment_id' => 'required',
            'service_id' => 'required',
            'city_id' => 'required',
            'location_id' => 'required',
            'doctor_id' => 'required',
            'resourceId' => 'required',
            'resourceRotaId' => 'required',
            'resourceRotaDayId' => 'required',
            'date' => 'required',
            'time' => 'required',
            'consultancy_type' => 'required',
        ]);
    }

    /**
     * Update Appointment Status
     *
     * @param \App\Http\Requests\Admin\StoreUpdateAppointmentsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeAppointmentStatuses(Request $request)
    {
        // $userinfo = User::find(Auth::user()->id);

        $data = $request->all();
// var_dump($data);exit;
        $invoicestatus = InvoiceStatuses::where('slug', '=', 'paid')->first();
        $appointment = Appointments::findOrFail($request->get('id'));

        $appointment_type = AppointmentTypes::where('slug', '=', 'consultancy')->first();
        $appointment_type_2 = AppointmentTypes::where('slug', '=', 'treatment')->first();

        $counterglobal = Settings::where('slug', '=', 'sys-appointmentrescheduledcounter')->first();

        $invoiceexit = Invoices::where([
            ['invoice_status_id', '=', $invoicestatus->id],
            ['appointment_id', '=', $data['id']]
        ])->get();

        if ($data['base_appointment_status_id'] == Config::get('constants.appointment_status_arrived')) {
            if (count($invoiceexit) == 0) {
                return response()->json(['status' => 0]);
            }
        }
        if ($data['base_appointment_status_id'] != Config::get('constants.appointment_status_arrived')) {
            if (count($invoiceexit) == 1) {
                return response()->json(['status' => 2]);
            }
        }

        if ($appointment_type->id == $appointment->appointment_type_id) {
            if ($appointment->base_appointment_status_id == Config::get('constants.appointment_status_not_interested')) {
                if ($data['base_appointment_status_id'] != Config::get('constants.appointment_status_not_interested')) {
                    $data['counter'] = 0;
                }
            }
        }

        // Set Allow Message Flag
        if (isset($data['base_appointment_status_id'])) {
            $appointment_status = AppointmentStatuses::getData($data['base_appointment_status_id']);
            if (!empty($appointment_status)) {
                $data['appointment_status_allow_message'] = $appointment_status->allow_message;
            } else {
                $data['appointment_status_allow_message'] = null;
            }

        }

        if (!isset($data['appointment_status_id']) || $data['appointment_status_id'] == '') {
            $data['appointment_status_id'] = $data['base_appointment_status_id'];
//            $data['reason'] = null;
        } else {
//            if (isset($data['reason']) && !$data['reason']) {
//                $data['reason'] = null;
//            }
        }

        // Set Comments
        if (isset($data['reason']) && !$data['reason']) {
            $data['reason'] = null;
        }
// var_dump(Auth::User()->id);exit;
        // Converted By
        $data['converted_by'] = $data['patient_id'];
        /*$data['updated_by'] = Auth::User()->id;*/
        $data['updated_at'] = Carbon::parse(Carbon::now())->toDateTimeString();


        if ($appointment_type->id == $appointment->appointment_type_id) {
            if ($data['base_appointment_status_id'] == Config::get('constants.appointment_status_not_show')) {
                if ($appointment->counter == $counterglobal->data) {
                    $data['base_appointment_status_id'] = Config::get('constants.appointment_status_not_interested');
                    $appointment_childstatus_not_interested = AppointmentStatuses::where('parent_id', '=', Config::get('constants.appointment_status_not_interested'))->first();
                    if ($appointment_childstatus_not_interested) {
                        $data['appointment_status_id'] = $appointment_childstatus_not_interested->id;
                    } else {
                        $data['appointment_status_id'] = Config::get('constants.appointment_status_not_interested');
                    }
                } else {
                    $data['counter'] = $appointment->counter + 1;
                }
            }
        }
        $appointment->update($data);

        if ($appointment_type->id == $appointment->appointment_type_id) {
            if ($data['base_appointment_status_id'] == Config::get('constants.appointment_status_not_show')) {
                if ($appointment->counter == $counterglobal->data) {
                    $data['base_appointment_status_id'] = Config::get('constants.appointment_status_not_interested');
                    $appointment_childstatus_not_interested = AppointmentStatuses::where('parent_id', '=', Config::get('constants.appointment_status_not_interested'))->first();
                    if ($appointment_childstatus_not_interested) {
                        $data['appointment_status_id'] = $appointment_childstatus_not_interested->id;
                    } else {
                        $data['appointment_status_id'] = Config::get('constants.appointment_status_not_interested');
                    }
                }
            }
        }
        $appointment->update($data);
        $appointment_status_name = AppointmentStatuses::where('id', '=', $data['base_appointment_status_id'])->first();

        /**
         * Dispatch Elastic Search Index
         */
//        $this->dispatch(
//            new IndexSingleAppointmentJob([
//                'account_id' => 1,// acoount id is hard coded 1
//                'appointment_id' => $appointment->id
//            ])
//        );

        return response()->json(['status' => 1, 'base_appointment_status_name' => $appointment_status_name->name]);
    }

    /**
     * Verify QR code
     *
     * @param \App\Http\Requests\Admin\StoreUpdateAppointmentsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function verifyqrcode_api(Request $request)
    {
        if ($request->is_scanned == 'yes') {
            $check_inv_qr = explode('-', $request->inv_qr);
            $inv_id = $check_inv_qr[0];
            $verifyqrcode = Invoices::where('id', $inv_id)->where('inv_qr', $request->inv_qr)->get();
            $action = 'Scan';
        } else {
            $inv_id = $request->inv_qr;
            $verifyqrcode = Invoices::where('id', $inv_id)->get();
            $action = 'Input';
        }
        $invoice_found = 'No';

        if (count($verifyqrcode) > 0) {
            /* Now We need to update is_scanned and sanned by in invoice table
             */
            $invoice_found = 'Yes';
            Invoices::where('id', '=', $inv_id)->update(['is_scanned' => 1, 'scanned_date' => Carbon::parse(Carbon::now())->toDateTimeString(), 'scanned_by' => (int)$request->scanned_by]);

            //* call function for display invoice

            $appointment = Appointments::displayInvoiceAppointment_api($inv_id);

            $response = response()->json([
                'status' => true,
                'message' => 'Record is present!',
                'data' => $appointment,
                'status_code' => 200,
            ]);

        } else {

            $response = response()->json([
                'status' => false,
                'message' => 'QR or Invoice are mismatched!',
                'status_code' => 200,
            ]);
        }
        /*insert data into invoice_scan_logs */
        $invoice_scan_logs = InvoiceScanLog::Create(
            [
                'user_id' => (int)$request->scanned_by,
                'invoice_id' => $inv_id,
                'action' => $action,
                'invoice_found' => $invoice_found,
                'inv_qr' => $request->inv_qr,
            ]
        );

        return $response;

    }

    /**
     * Define the count for day and time to show while booking consultancy
     */
    public function getdaytimecount() {

        $day = Settings::where('slug', '=', 'sys-consultancy-count-day-values')->first();
        $day = explode(',', $day->data);

        $day_count = array();
        for ($i = 0; $i < count($day); $i++) {
            $day_count[] = (int) $day[$i];
        }

        $time = Settings::where('slug', '=', 'sys-consultancy-count-time-values')->first();
        $time = explode(',', $time->data);

        $time_count = array();
        for ($i = 0; $i < count($time); $i++) {
            $time_count[] = (int) $time[$i];
        }

        return response()->json([
            'status' => true,
            'message' => 'Day and time information successfully given',
            'count' => array(
                'day_count' => $day_count,
                'time_count' => $time_count
            ),
            'status_code' => 200,
        ]);
    }

    /**
     * Display the pdf file
     * */
    public function get_appointmentinvoice_pdf($id)
    {
        $Invoiceinfo = DB::table('invoices')
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->where('invoices.id', '=', $id)
            ->select('invoices.*',
                'invoice_details.discount_type',
                'invoice_details.discount_price',
                'invoice_details.service_price',
                'invoice_details.net_amount',
                'invoice_details.service_id',
                'invoice_details.discount_id',
                'invoice_details.package_id',
                'invoice_details.invoice_id',
                'invoice_details.tax_exclusive_serviceprice',
                'invoice_details.tax_percenatage',
                'invoice_details.tax_price',
                'invoice_details.tax_including_price',
                'invoice_details.is_exclusive'
            )
            ->first();

        $appointment_info = Appointments::where('id','=',$Invoiceinfo->appointment_id)->first();

        $location_info = Locations::find($Invoiceinfo->location_id);

        $invoicestatus = InvoiceStatuses::find($Invoiceinfo->invoice_status_id);
        if ($Invoiceinfo->discount_id) {
            $discount = Discounts::find($Invoiceinfo->discount_id);
        } else {
            $discount = null;
        }
        $service = Services::find($Invoiceinfo->service_id);
        $patient = User::find($Invoiceinfo->patient_id);
        $account = Accounts::find($Invoiceinfo->account_id);
        $company_phone_number = Settings::where('slug', '=', 'sys-headoffice')->first();

        if($appointment_info->appointment_type_id == 1){
            $content = view('admin.invoices.invoice_pdf', compact('id','Invoiceinfo', 'patient', 'account', 'service', 'discount', 'invoicestatus', 'company_phone_number', 'location_info'))->render();
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($content);
            return $pdf->stream('Consultation Invoice');
        } else {
            $content = view('admin.invoices.invoice_pdf', compact('id','Invoiceinfo', 'patient', 'account', 'service', 'discount', 'invoicestatus', 'company_phone_number', 'location_info'))->render();
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($content);
            $this->PDFFileStore($pdf, $id);

            return $pdf->stream('Treatment Invoice');
        }
    }
}
