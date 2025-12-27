<?php

namespace App\Models;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Helpers\NodesTree;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Helpers\GeneralFunctions;
use Illuminate\Support\Facades\Gate;
use DB;

class Appointments extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'scheduled_date',
        'scheduled_time',
        'scheduled_at_count',
        'first_scheduled_date',
        'first_scheduled_time',
        'first_scheduled_count',
        'active',
        'name',
        'account_id',
        'appointment_type_id',
        'base_appointment_status_id',
        'created_by',
        'updated_by',
        'converted_by',
        'msg_count',
        'lead_id',
        'patient_id',
        'send_message',
        'appointment_status_allow_message',
        'appointment_status_id',
        'service_id',
        'cancellation_reason_id',
        'reason',
        'resource_id',
        'resource_has_rota_day_id',
        'resource_has_rota_day_id_for_machine',
        'doctor_id',
        'region_id',
        'city_id',
        'location_id',
        'created_at',
        'updated_at',
        'appointment_id',
        'counter',
        'consultancy_type',
        'coming_from',
        'source',
        'is_converted'
    ];

    protected $table = 'appointments';
    protected static $_table = 'appointments';
    /**
     * used in event
     * @var string
     */
    public $__table = 'appointments';
    static protected $_fillable = [
        'scheduled_date',
        'scheduled_time',
        'scheduled_at_count',
        'first_scheduled_date',
        'first_scheduled_time',
        'first_scheduled_count',
        'active',
        'name',
        'account_id',
        'appointment_type_id',
        'base_appointment_status_id',
        'created_by',
        'updated_by',
        'converted_by',
        'msg_count',
        'lead_id',
        'patient_id',
        'send_message',
        'appointment_status_allow_message',
        'appointment_status_id',
        'service_id',
        'cancellation_reason_id',
        'reason',
        'resource_id',
        'resource_has_rota_day_id',
        'resource_has_rota_day_id_for_machine',
        'doctor_id',
        'region_id',
        'city_id',
        'location_id',
        'created_at',
        'updated_at',
        'appointment_id',
        'counter',
        'consultancy_type',
        'coming_from',
        'source'
    ];

    /**
     * used in events
     * @var array
     */
    public $__fillable = [
        'scheduled_date',
        'scheduled_time',
        'scheduled_at_count',
        'first_scheduled_date',
        'first_scheduled_time',
        'first_scheduled_count',
        'active',
        'name',
        'account_id',
        'appointment_type_id',
        'base_appointment_status_id',
        'created_by',
        'updated_by',
        'converted_by',
        'msg_count',
        'lead_id',
        'patient_id',
        'send_message',
        'appointment_status_allow_message',
        'appointment_status_id',
        'service_id',
        'cancellation_reason_id',
        'reason',
        'resource_id',
        'resource_has_rota_day_id',
        'resource_has_rota_day_id_for_machine',
        'doctor_id',
        'region_id',
        'city_id',
        'location_id',
        'created_at',
        'updated_at',
        'appointment_id',
        'counter',
        'consultancy_type',
        'coming_from'
    ];

    protected $attributes = array(
        'consultancy_type' => 'in_person'
    );

    public static function updateServiceRecord($id, $data, $account_id)
    {
        // Set Account ID
        $data['account_id'] = $account_id;
        $data['updated_at'] = Carbon::parse(Carbon::now())->toDateTimeString();
        $data['updated_by'] = Auth::User()->id;

        if (isset($data['start'])) {
            $data['scheduled_date'] = Carbon::parse($data['start'])->format('Y-m-d');
            $data['scheduled_time'] = Carbon::parse($data['start'])->format('H:i:s');
            if ($data['first_scheduled_count'] == 0) {
                $data['first_scheduled_date'] = Carbon::parse($data['start'])->format('Y-m-d');
                $data['first_scheduled_time'] = Carbon::parse($data['start'])->format('H:i:s');
                $data['first_scheduled_count'] = 1;
            } else {
                $data['scheduled_at_count'] = $data['scheduled_at_count'] + 1;
            }
        } else {
            $data['scheduled_date'] = null;
            $data['scheduled_time'] = null;
            $data['first_scheduled_at'] = null;
        }
        if (isset($data["resourceId"])) {
            $data["resource_id"] = $data["resourceId"];
        }

        $record = self::where([
            'id' => $id,
            'account_id' => $account_id
        ])->first();

        if (!$record) {
            return null;
        }

        $record->update($data);

        self::appointmentReschedule($id, Auth::User()->id, $data['start'], $data['start']);
        return $record;
    }

    /**
     * Get the lead comments for lead.
     */
    public function appointment_comments()
    {
        return $this->hasMany('App\Models\AppointmentComments', 'appointment_id')->OrderBy('created_at', 'desc');
    }

    /**
     * Get the Service that owns the Appointment.
     */
    public function service()
    {
        return $this->belongsTo('App\Models\Services')->withTrashed();
    }

    /**
     * Get Appointment Type that owns the Appointment.
     */
    public function appointment_type()
    {
        return $this->belongsTo('App\Models\AppointmentTypes')->withTrashed();
    }

    /**
     * Get the Appointment Status that owns the Appointment.
     */
    public function appointment_status()
    {
        return $this->belongsTo('App\Models\AppointmentStatuses')->withTrashed();
    }

    /*
     * Get the Appointment status according to base appointment status
     * */

    public function appointment_status_base()
    {
        return $this->belongsTo('App\Models\AppointmentStatuses', 'base_appointment_status_id')->withTrashed();
    }

    /**
     * Get the Appointment Status that owns the Appointment.
     */
    public function cancellation_reason()
    {
        return $this->belongsTo('App\Models\CancellationReasons')->withTrashed();
    }

    /**
     * Get the Doctors that owns the Appointment.
     */
    public function doctor()
    {
        return $this->belongsTo('App\User', 'doctor_id')->withTrashed();
    }

    /**
     * Get the City that owns the Appointment.
     */
    public function city()
    {
        return $this->belongsTo('App\Models\Cities')->withTrashed();
    }

    /**
     * Get the Region that owns the Appointment.
     */
    public function region()
    {
        return $this->belongsTo('App\Models\Regions')->withTrashed();
    }

    /**
     * Get the Doctors that owns the Appointment.
     */
    public function location()
    {
        return $this->belongsTo('App\Models\Locations')->withTrashed();
    }

    /**
     * Get the Lead that owns the Appointment.
     */
    public function lead()
    {
        return $this->belongsTo('App\Models\Leads')->withTrashed();
    }

    public function appointment_lead()
    {
        return $this->hasOne('App\Models\Leads', 'id', 'lead_id');
    }

    /**
     * Get the patient that owns the Appointment.
     */
    public function patient()
    {
        return $this->belongsTo('App\User', 'patient_id')->withTrashed();
    }

    /**
     * Get the user that create appointment.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'created_by')->withTrashed();
    }

    /*
     * Get the user who updated the appointment
     */
    public function user_updated_by()
    {
        return $this->belongsTo('App\User', 'updated_by')->withTrashed();
    }

    /*
     * Get the user who convert the appointment
     */
    public function user_converted_by()
    {
        return $this->belongsTo('App\User', 'converted_by')->withTrashed();
    }

    static public function updateRecordAPI($id, $data, $account_id)
    {
        // Set Account ID
        $data['account_id'] = $account_id;
        $data['updated_at'] = Carbon::parse(Carbon::now())->toDateTimeString();
        $data['updated_by'] = 1;

        if (isset($data['start'])) {
            $data['scheduled_date'] = Carbon::parse($data['start'])->format('Y-m-d');
            $data['scheduled_time'] = Carbon::parse($data['start'])->format('H:i:s');
            if ($data['first_scheduled_count'] == 0) {
                $data['first_scheduled_date'] = Carbon::parse($data['start'])->format('Y-m-d');
                $data['first_scheduled_time'] = Carbon::parse($data['start'])->format('H:i:s');
                $data['first_scheduled_count'] = 1;
            } else {
                $data['scheduled_at_count'] = $data['scheduled_at_count'] + 1;
            }
        } else {
            $data['scheduled_date'] = null;
            $data['scheduled_time'] = null;
            $data['first_scheduled_at'] = null;
        }

        $record = self::where([
            'id' => $id,
            'account_id' => $account_id
        ])->first();

        if (!$record) {
            return null;
        }

        $record->update($data);

        return $record;
    }


    /*
     * Get the appointments for City.
     */
    public function sms_logs()
    {
        return $this->hasMany('App\Models\SMSLogs', 'appointment_id')->withTrashed();
    }

    /*
     * Self join on appointment_id
     * */

    public function appointments()
    {
        return $this->hasMany(Appointments::class, 'appointment_id');
    }

    /**
     * Get the package advances information.
     */
    public function packageadvance()
    {

        return $this->hasMany('App\Models\PackageAdvances', 'appointment_id');
    }

    /*
     * Get the packages information
     * */

    public function packages()
    {
        return $this->hasMany(Packages::class, 'appointment_id');
    }

    /*
     * Get the invoices of the appointments
     * */
    public function hasInvoices()
    {
        return $this->hasMany(Invoices::class, 'appointment_id');
    }

    /**
     * Prepare SMS Contnet for Delivery
     *
     * @param: int $appointment_id
     * @param: int $smsContent
     *
     * @return: string
     */
    static public function prepareSMSContent($appointment_id = false, $smsContent)
    {
        if (!$appointment_id) {
            return $smsContent;
        } else {
            $appointment = self::find($appointment_id);
            $patient = Patients::find($appointment->patient_id);

            // Load Globar Setting for Head Office
            $Setting = Settings::getBySlug('sys-headoffice', $appointment->account_id);
            $smsContent = str_replace('##head_office_phone##', $Setting->data, $smsContent);

            if ($appointment) {
                // Replace Patient Information
                $smsContent = str_replace('##patient_name##', ($appointment->name) ? $appointment->name : $patient->name, $smsContent);
                $smsContent = str_replace('##patient_phone##', $patient->phone, $smsContent);

                // Replace Schedule Information
                $smsContent = str_replace('##appointment_date##', Carbon::parse($appointment->scheduled_date)->format('l, F d,Y'), $smsContent);
                $smsContent = str_replace('##appointment_time##', Carbon::parse($appointment->scheduled_time)->format('h:i A'), $smsContent);

                // Replace Service Information
                $service = Services::find($appointment->service_id);
                if ($service) {
                    $smsContent = str_replace('##appointment_service##', $service->name, $smsContent);
                }

                // Load and Replace Centre Information
                $Location = Locations::find($appointment->location_id);
                if ($Location) {
                    $smsContent = str_replace('##fdo_name##', $Location->fdo_name, $smsContent);
                    $smsContent = str_replace('##fdo_phone##', GeneralFunctions::prepareNumber4Call($Location->fdo_phone), $smsContent);
                    $smsContent = str_replace('##centre_name##', $Location->name, $smsContent);
                    $smsContent = str_replace('##centre_address##', $Location->address, $smsContent);
                    $smsContent = str_replace('##centre_google_map##', $Location->google_map, $smsContent);
                }

                // Load and Replace Doctor Information
                $Doctor = Doctors::find($appointment->doctor_id);
                if ($Doctor) {
                    $smsContent = str_replace('##doctor_name##', $Doctor->name, $smsContent);
                    $smsContent = str_replace('##doctor_profile_link##', $Doctor->profile_url, $smsContent);

                    if ($appointment->consultancy_type == 'virtual') {
                        $smsContent = str_replace('##virtual_link##', $Doctor->virtual_link ? $Doctor->virtual_link : '', $smsContent);
                    }
                }
            }
            // $date = Carbon::parse($appointment->scheduled_date)->format('l, F d,Y');
            // $time = Carbon::parse($appointment->scheduled_time)->format('h:i A');
            // $smsContent = "Dear $appointment->name, thank you for booking your treatment with 3D lifestyle. Your session details are \nDate:.$date.\nTime: $time\nAddress: .$Location->address.\nCenter Number: +92$Location->fdo_phone\nUAN: $Setting->data\n Website: http://3dlifestyle.pk/\nMap Directions: .$Location->google_map.\nTo block promotions from 3Dlifestyle send UNSUB to \nTo block all promotions, send REG to 3627\n";

            return $smsContent;
        }
    }

    /**
     * Get Doctor based appointments
     *
     * @param: \Illuminate\Http\Request $request
     * @param: $account_id Current organization id
     *
     * @return: string
     */
    static function getNonScheduledAppointments(Request $request, $appointment_type_id = false, $account_id)
    {
        $where = array();
        $where[] = ['account_id', '=', $account_id];

        /*
         * Get default cancelled appointment status
         */
        $cancelled_appointment_status = AppointmentStatuses::getCancelledStatusOnly($account_id);
        if ($cancelled_appointment_status) {
            $where[] = ['base_appointment_status_id', '!=', $cancelled_appointment_status->id];
        }

        if ($appointment_type_id) {
            $where[] = ['appointment_type_id', '=', $appointment_type_id];
        }

        if ($request->get('city_id')) {
            $where[] = ['city_id', '=', $request->get('city_id')];
        }

        if ($request->get('location_id')) {
            $where[] = ['location_id', '=', $request->get('location_id')];
        }

        if ($request->get('doctor_id')) {
            $where[] = ['doctor_id', '=', $request->get('doctor_id')];
        }

        return self::where($where)
            ->whereNull('scheduled_date')
            ->whereNull('scheduled_time')
            ->get();
    }

    /**
     * Get Doctor based appointments
     *
     * @param: \Illuminate\Http\Request $request
     * @param: integer $appointment_type_id Appointment ID
     * @param: integer $account_id Current organization id
     * @param: boolean $skip_doctor
     *
     * @return: string
     */
    static function getScheduledAppointments(Request $request, $appointment_type_id = false, $account_id, $skip_doctor = false)
    {
        $where = array();
        $where[] = ['account_id', '=', $account_id];

        /*
         * Get default cancelled appointment status
         */
        $cancelled_appointment_status = AppointmentStatuses::getCancelledStatusOnly($account_id);
        if ($cancelled_appointment_status) {
            $where[] = ['base_appointment_status_id', '!=', $cancelled_appointment_status->id];
        }

        if ($appointment_type_id) {
            $where[] = ['appointment_type_id', '=', $appointment_type_id];
        }

        if ($request->get('city_id')) {
            $where[] = ['city_id', '=', $request->get('city_id')];
        }

        if ($request->get('start')) {
            $where[] = ['scheduled_date', '>=', Carbon::parse($request->get('start'))->format('Y-m-d')];
        }

        if ($request->get('location_id')) {
            $where[] = ['location_id', '=', $request->get('location_id')];
        }

        if (!$skip_doctor) {
            if ($request->get('doctor_id')) {
                $where[] = ['doctor_id', '=', $request->get('doctor_id')];
            }
        }

        return self::where($where)
            ->whereNotNull('scheduled_date')
            ->whereNotNull('scheduled_time')
            // these lines skiping records because data from request is is date and matching with time
            /*            ->whereDate('scheduled_time', '>=', Carbon::parse($request->get('start'))->toDateString())
                        ->whereDate('scheduled_time', '<=', Carbon::parse($request->get('end'))->toDateString())*/
            ->get();
    }


    static function getScheduledAppointmentsSpecificdate(Request $request, $appointment_type_id = false, $account_id, $skip_doctor = false)
    {
        $where = array();
        $where[] = ['account_id', '=', $account_id];

        /*
         * Get default cancelled appointment status
         */
        $cancelled_appointment_status = AppointmentStatuses::getCancelledStatusOnly($account_id);
        if ($cancelled_appointment_status) {
            $where[] = ['base_appointment_status_id', '!=', $cancelled_appointment_status->id];
        }

        if ($appointment_type_id) {
            $where[] = ['appointment_type_id', '=', $appointment_type_id];
        }

        if ($request->get('city_id')) {
            $where[] = ['city_id', '=', $request->get('city_id')];
        }


        if ($request->get('location_id')) {
            $where[] = ['location_id', '=', $request->get('location_id')];
        }

        if ($request->get('date')) {
            $where[] = ['scheduled_date', '=', $request->get('date')];
        }

        if (!$skip_doctor) {
            if ($request->get('doctor_id')) {
                $where[] = ['doctor_id', '=', $request->get('doctor_id')];
            }
        }

        return self::where($where)
            ->whereNotNull('scheduled_date')
            ->whereNotNull('scheduled_time')
            // these lines skiping records because data from request is is date and matching with time
            /*            ->whereDate('scheduled_time', '>=', Carbon::parse($request->get('start'))->toDateString())
                        ->whereDate('scheduled_time', '<=', Carbon::parse($request->get('end'))->toDateString())*/
            ->get();
    }


    static function getScheduledAppointmentsBetweenDateRange(Request $request, $doctor_id)
    {
        $where = array();
        $where[] = ['account_id', '=', 1];

        /*
         * Get default cancelled appointment status
         */
        $cancelled_appointment_status = AppointmentStatuses::getCancelledStatusOnly(1);
        if ($cancelled_appointment_status) {
            $where[] = ['base_appointment_status_id', '!=', $cancelled_appointment_status->id];
        }

        if ($request->get('city_id')) {
            $where[] = ['city_id', '=', $request->get('city_id')];
        }

        if ($request->get('location_id')) {
            $where[] = ['location_id', '=', $request->get('location_id')];
        }

        return self::where($where)
            ->whereBetween('scheduled_date', [$request->get('start'), $request->get('end')])
            ->where('doctor_id', $doctor_id)
            ->whereNotNull('scheduled_date')
            ->whereNotNull('scheduled_time')
            // these lines skiping records because data from request is is date and matching with time
            /*            ->whereDate('scheduled_time', '>=', Carbon::parse($request->get('start'))->toDateString())
                        ->whereDate('scheduled_time', '<=', Carbon::parse($request->get('end'))->toDateString())*/
            ->get();
    }

    static function getUserScheduledAppointments()
    {
        return self::where('created_by', Auth::id())->get();
    }

    /**
     * Update Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function updateRecord($id, $data, $account_id)
    {
        // Set Account ID
        $data['account_id'] = $account_id;
        $data['updated_at'] = Carbon::parse(Carbon::now())->toDateTimeString();
        $data['updated_by'] = Auth::User()->id;

        if (isset($data['start'])) {
            $data['scheduled_date'] = Carbon::parse($data['start'])->format('Y-m-d');
            $data['scheduled_time'] = Carbon::parse($data['start'])->format('H:i:s');
            if ($data['first_scheduled_count'] == 0) {
                $data['first_scheduled_date'] = Carbon::parse($data['start'])->format('Y-m-d');
                $data['first_scheduled_time'] = Carbon::parse($data['start'])->format('H:i:s');
                $data['first_scheduled_count'] = 1;
            } else {
                $data['scheduled_at_count'] = $data['scheduled_at_count'] + 1;
            }
        } else {
            $data['scheduled_date'] = null;
            $data['scheduled_time'] = null;
            $data['first_scheduled_at'] = null;
        }

        $record = self::where([
            'id' => $id,
            'account_id' => $account_id
        ])->first();

        if (!$record) {
            return null;
        }

        $record->update($data);

        self::appointmentReschedule($id, Auth::User()->id, $data['start'], $data['start']);

        return $record;
    }

    /**
     * Get Node Services
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function getNodeServices($serviceId = 0, $account_id, $drop_down = false, $remove_spaces = false)
    {
        /*
         * That function use Appointment Report (Appointment by status) and Treatment Management
         */
        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(($serviceId) ? $serviceId : 0, $account_id, true, true);
        $parentGroups->toList($parentGroups, -1);
        $services = $parentGroups->nodeList;

        $nodeList = array();

        if ($drop_down) {
            $nodeList[''] = 'Select a Child Service';
        }

        if (count($services)) {
            foreach ($services as $key => $service) {
                if ($key < 0) {
                    continue;
                }

                if ($drop_down) {
                    if ($remove_spaces) {
                        $nodeList[$key] = str_replace("&nbsp;", '', trim($service['name']));
                    } else {
                        $nodeList[$key] = trim($service['name']);
                    }
                } else {
                    if ($remove_spaces) {
                        $service['name'] = str_replace("&nbsp;", '', trim($service['name']));
                    }
                    $nodeList[$key] = $service;
                }
            }
        }

        return $nodeList;
    }

    public static function boot()
    {


        parent::boot();


        static::created(function ($item) {

            Event::fire('appointment.created', $item);
        });


        static::updating(function ($item) {

            Event::fire('appointment.updating', $item);
        });


        static::deleting(function ($item) {

            Event::fire('appointment.deleting', $item);
        });
    }

    /**
     * Delete Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function DeleteRecord($id, $account_id)
    {
        $appointment = self::where(['id' => $id, 'account_id' => $account_id])->first();

        if (!$appointment) {

            flash('Appointment not found.')->error()->important();
            return redirect()->route('admin.appointments.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (self::isChildExists($id, $account_id)) {
            flash('Child records exist, unable to delete appointment')->error()->important();
            return redirect()->route('admin.appointments.index');
        }


        $record = $appointment->delete();

        //log request for delete for audit trail
        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

        return $record;
    }

    /**
     * Check if child records exist
     *
     * @param (int) $id
     * @param
     *
     * @return (boolean)
     */
    static protected function isChildExists($id, $account_id)
    {
        if (
            PackageAdvances::where(['appointment_id' => $id, 'account_id' => $account_id])->count() ||
            Invoices::where(['appointment_id' => $id, 'account_id' => $account_id])->count() ||
            Measurement::where(['appointment_id' => $id])->count() ||
            Appointmentimage::where(['appointment_id' => $id])->count()
        ) {
            return true;
        }

        return false;
    }

    /*
     * Get the Appointment count for datatable
     */
    static public function getTotalRecords(Request $request, $account_id = false, $apply_filter = false)
    {
        $where = self::appointments_filters_count($request, $account_id, $apply_filter);
        return self::fetchRecoordcount($where);
    }

    /*
     * Get the Appointment for datatable
     */
    static function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false, $apply_filter = false)
    {
        $where = self::appointments_filters($request, $account_id, $apply_filter);
        $result = self::fetchRecoord($where);
        if ($request->get('order')) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            if ($orderBy == 'created_at') {
                $orderBy = 'appointments.created_at';
            }
            $order = $request->get('order')[0]['dir'];

            Filters::put(Auth::User()->id, 'appointments', 'order_by', $orderBy);
            Filters::put(Auth::User()->id, 'appointments', 'order', $order);
        } else {
            if (
                Filters::get(Auth::User()->id, 'appointments', 'order_by')
                && Filters::get(Auth::User()->id, 'appointments', 'order')
            ) {
                $orderBy = Filters::get(Auth::User()->id, 'appointments', 'order_by');
                $order = Filters::get(Auth::User()->id, 'appointments', 'order');

                if ($orderBy == 'created_at') {
                    $orderBy = 'appointments.created_at';
                }
            } else {
                $orderBy = 'created_at';
                $order = 'desc';
                if ($orderBy == 'created_at') {
                    $orderBy = 'appointments.created_at';
                }

                Filters::put(Auth::User()->id, 'appointments', 'order_by', $orderBy);
                Filters::put(Auth::User()->id, 'appointments', 'order', $order);
            }
        }
        return $result->select('users.phone', 'appointments.*', 'appointments.name as patient_name', 'appointments.id as app_id', 'appointments.created_by as app_created_by', 'appointments.updated_by as app_updated_by', 'appointments.created_at as app_created_at', 'leads.lead_source_id', 'leads.town_id')
            ->limit($iDisplayLength)
            ->offset($iDisplayStart)
            ->orderBy($orderBy, $order)
            ->get();
    }

    /*
     * Main query for count for datatable
     */
    static function fetchRecoordcount($where)
    {
        $consultancyslug = AppointmentTypes::where('slug', '=', 'consultancy')->first();
        $treatmentslug = AppointmentTypes::where('slug', '=', 'treatment')->first();

        if (Gate::allows('appointments_consultancy') && !Gate::allows('appointments_services')) {
            return Appointments::whereHas('patient', function ($patient) use ($where) {
                $patient->where($where[0]);
            })->whereHas('lead', function ($lead) use ($where) {
                $lead->where($where[1]);
            })
                ->where('appointments.appointment_type_id', '=', $consultancyslug->id)
                ->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres())
                ->where(function ($query) use ($where) {
                    foreach ($where[2] as $condition) {
                        if ($condition[1] === 'in') {
                            $query->whereIn($condition[0], $condition[2]);
                        } else {
                            $query->where($condition[0], $condition[1], $condition[2]);
                        }
                    }
                })
                ->count();
        }
        if (Gate::allows('appointments_services') && !Gate::allows('appointments_consultancy')) {
            return Appointments::whereHas('patient', function ($patient) use ($where) {
                $patient->where($where[0]);
            })->whereHas('lead', function ($lead) use ($where) {
                $lead->where($where[1]);
            })
                ->where('appointments.appointment_type_id', '=', $treatmentslug->id)
                ->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres())
                ->where(function ($query) use ($where) {
                    foreach ($where[2] as $condition) {
                        if ($condition[1] === 'in') {
                            $query->whereIn($condition[0], $condition[2]);
                        } else {
                            $query->where($condition[0], $condition[1], $condition[2]);
                        }
                    }
                })
                ->count();
        }
        if (Gate::allows('appointments_services') && Gate::allows('appointments_consultancy')) {
            return Appointments::whereHas('patient', function ($patient) use ($where) {
                $patient->where($where[0]);
            })->whereHas('lead', function ($lead) use ($where) {
                $lead->where($where[1]);
            })
                ->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres())
                ->where(function ($query) use ($where) {
                    foreach ($where[2] as $condition) {
                        if ($condition[1] === 'in') {
                            $query->whereIn($condition[0], $condition[2]);
                        } else {
                            $query->where($condition[0], $condition[1], $condition[2]);
                        }
                    }
                })
                ->count();
        }
        if (!Gate::allows('appointments_services') && !Gate::allows('appointments_consultancy')) {
            return Appointments::whereHas('patient', function ($patient) use ($where) {
                $patient->where($where[0]);
            })->whereHas('lead', function ($lead) use ($where) {
                $lead->where($where[1]);
            })->where([
                ['appointments.appointment_type_id', '!=', $consultancyslug->id],
                ['appointments.appointment_type_id', '!=', $treatmentslug->id]
            ])
                ->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres())
                ->where(function ($query) use ($where) {
                    foreach ($where[2] as $condition) {
                        if ($condition[1] === 'in') {
                            $query->whereIn($condition[0], $condition[2]);
                        } else {
                            $query->where($condition[0], $condition[1], $condition[2]);
                        }
                    }
                })
                ->count();
        }
    }

    /*
     * Main query for data for datatable
     */
    static function fetchRecoord($where)
    {

        $consultancyslug = AppointmentTypes::where('slug', '=', 'consultancy')->first();
        $treatmentslug = AppointmentTypes::where('slug', '=', 'treatment')->first();

        if (Gate::allows('appointments_consultancy') && !Gate::allows('appointments_services')) {
            $result = Appointments::join('users', function ($join) {
                $join->on('users.id', '=', 'appointments.patient_id');
            })->join('leads', function ($join) {
                $join->on('leads.id', '=', 'appointments.lead_id');
            })->where('appointments.appointment_type_id', '=', $consultancyslug->id)
                ->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres());
        }
        if (Gate::allows('appointments_services') && !Gate::allows('appointments_consultancy')) {
            $result = Appointments::join('users', function ($join) {
                $join->on('users.id', '=', 'appointments.patient_id');
            })->join('leads', function ($join) {
                $join->on('leads.id', '=', 'appointments.lead_id');
            })->where('appointments.appointment_type_id', '=', $treatmentslug->id)
                ->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres());
        }
        if (Gate::allows('appointments_services') && Gate::allows('appointments_consultancy')) {
            $result = Appointments::join('users', function ($join) {
                $join->on('users.id', '=', 'appointments.patient_id');
            })->join('leads', function ($join) {
                $join->on('leads.id', '=', 'appointments.lead_id');
            })->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres());
        }
        if (!Gate::allows('appointments_services') && !Gate::allows('appointments_consultancy')) {
            $result = Appointments::join('users', function ($join) {
                $join->on('users.id', '=', 'appointments.patient_id');
            })->join('leads', function ($join) {
                $join->on('leads.id', '=', 'appointments.lead_id');
            })->where([
                ['appointments.appointment_type_id', '!=', $consultancyslug->id],
                ['appointments.appointment_type_id', '!=', $treatmentslug->id]
            ])
                ->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres());
        }
        return $result->where(function ($query) use ($where) {
            foreach ($where as $condition) {
                if ($condition[1] === 'in') {
                    $query->whereIn($condition[0], $condition[2]);
                } else {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
        });
    }

    /*
     * Get the filters for data
     */
    static function appointments_filters($request, $account_id, $apply_filter)
    {
        $where = array();

        $where[] = array(
            'users.user_type_id',
            '=',
            config('constants.patient_id')
        );
        if ($request->get('patient_id') && $request->get('patient_id') != '') {
            $where[] = array(
                'users.id',
                '=',
                $request->get('patient_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'patient_id', $request->get('patient_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'patient_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'patient_id')) {
                    $where[] = array(
                        'users.id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'patient_id')
                    );
                }
            }
        }
        if ($request->get('name') && $request->get('name') != '') {
            $where[] = array(
                'users.name',
                'like',
                '%' . $request->get('name') . '%'
            );
            Filters::put(Auth::User()->id, 'appointments', 'name', $request->get('name'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'name');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'name')) {
                    $where[] = array(
                        'users.name',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'name')
                    );
                }
            }
        }
        if ($request->get('phone') && $request->get('phone') != '') {
            $where[] = array(
                'users.phone',
                'like',
                '%' . GeneralFunctions::cleanNumber($request->get('phone')) . '%'
            );
            Filters::put(Auth::User()->id, 'appointments', 'phone', $request->get('phone'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'phone');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'phone')) {
                    $where[] = array(
                        'users.phone',
                        'like',
                        '%' . GeneralFunctions::cleanNumber(Filters::get(Auth::User()->id, 'appointments', 'phone')) . '%'
                    );
                }
            }
        }
        if ($request->get('date_from') && $request->get('date_from') != '') {
            $where[] = array(
                'appointments.scheduled_date',
                '>=',
                $request->get('date_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, 'appointments', 'date_from', $request->get('date_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'date_from');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'date_from')) {
                    $where[] = array(
                        'appointments.scheduled_date',
                        '>=',
                        Filters::get(Auth::User()->id, 'appointments', 'date_from')
                    );
                }
            }
        }
        if ($request->get('date_to') && $request->get('date_to') != '') {
            $where[] = array(
                'appointments.scheduled_date',
                '<=',
                $request->get('date_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, 'appointments', 'date_to', $request->get('date_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'date_to');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'date_to')) {
                    $where[] = array(
                        'appointments.scheduled_date',
                        '<=',
                        Filters::get(Auth::User()->id, 'appointments', 'date_to')
                    );
                }
            }
        }
        if ($request->get('doctor_id') && $request->get('doctor_id') != '') {
            $where[] = array(
                'appointments.doctor_id',
                '=',
                $request->get('doctor_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'doctor_id', $request->get('doctor_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'doctor_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'doctor_id')) {
                    $where[] = array(
                        'appointments.doctor_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'doctor_id')
                    );
                }
            }
        }
        if ($request->get('region_id') && $request->get('region_id') != '') {
            $where[] = array(
                'appointments.region_id',
                '=',
                $request->get('region_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'region_id', $request->get('region_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'region_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'region_id')) {
                    $where[] = array(
                        'appointments.region_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'region_id')
                    );
                }
            }
        }
        if ($request->get('city_id') && $request->get('city_id') != '') {
            $where[] = array(
                'appointments.city_id',
                '=',
                $request->get('city_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'city_id', $request->get('city_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'city_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'city_id')) {
                    $where[] = array(
                        'appointments.city_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'city_id')
                    );
                }
            }
        }
        if ($request->get('town_id') && $request->get('town_id') != '') {
            $where[] = array(
                'leads.town_id',
                '=',
                $request->get('town_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'town_id', $request->get('town_id'));
        } else {
            if ($apply_filter) {

                Filters::forget(Auth::User()->id, 'appointments', 'town_id');
            } else {

                if (Filters::get(Auth::User()->id, 'appointments', 'town_id')) {
                    $where[] = array(
                        'leads.town_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'town_id')
                    );
                }
            }
        }
        if ($request->get('location_id') && $request->get('location_id') != '') {
            $where[] = array(
                'appointments.location_id',
                '=',
                $request->get('location_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'location_id', $request->get('location_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'location_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'location_id')) {
                    $where[] = array(
                        'appointments.location_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'location_id')
                    );
                }
            }
        }
        if ($request->get('lead_source_id') && $request->get('lead_source_id') != '') {
            $where[] = array(
                'leads.lead_source_id',
                '=',
                $request->get('lead_source_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'lead_source_id', $request->get('lead_source_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'lead_source_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'lead_source_id')) {
                    $where[] = array(
                        'leads.lead_source_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'lead_source_id')
                    );
                }
            }
        }
        if ($request->get('service_id') && $request->get('service_id') != '') {
            $where[] = array(
                'appointments.service_id',
                '=',
                $request->get('service_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'service_id', $request->get('service_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'service_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'service_id')) {
                    $where[] = array(
                        'appointments.service_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'service_id')
                    );
                }
            }
        }
        if ($request->get('appointment_status_id') && $request->get('appointment_status_id') != '') {
            $where[] = array(
                'appointments.base_appointment_status_id',
                '=',
                $request->get('appointment_status_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'appointment_status_id', $request->get('appointment_status_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'appointment_status_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'appointment_status_id')) {
                    $where[] = array(
                        'appointments.base_appointment_status_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'appointment_status_id')
                    );
                }
            }
        }
        if ($request->get('buisness_status_id') && $request->get('buisness_status_id') != '') {
            $where[] = array(
                'appointments.buisness_status_id',
                '=',
                $request->get('buisness_status_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'buisness_status_id', $request->get('buisness_status_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'buisness_status_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'buisness_status_id')) {
                    $where[] = array(
                        'appointments.buisness_status_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'buisness_status_id')
                    );
                }
            }
        }
        if ($request->get('appointment_type_id') && $request->get('appointment_type_id') != '') {
            $where[] = array(
                'appointments.appointment_type_id',
                '=',
                $request->get('appointment_type_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'appointment_type_id', $request->get('appointment_type_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'appointment_type_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'appointment_type_id')) {
                    $where[] = array(
                        'appointments.appointment_type_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'appointment_type_id')
                    );
                }
            }
        }
        if ($request->get('consultancy_type') && $request->get('consultancy_type') != '') {
            $where[] = array(
                'appointments.consultancy_type',
                '=',
                $request->get('consultancy_type')
            );
            Filters::put(Auth::User()->id, 'appointments', 'consultancy_type', $request->get('consultancy_type'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'consultancy_type');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'consultancy_type')) {
                    $where[] = array(
                        'appointments.consultancy_type',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'consultancy_type')
                    );
                }
            }
        }
        if ($request->get('created_from') && $request->get('created_from') != '') {
            $where[] = array(
                'appointments.created_at',
                '>=',
                $request->get('created_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, 'appointments', 'created_from', $request->get('created_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'created_from')) {
                    $where[] = array(
                        'appointments.created_at',
                        '>=',
                        Filters::get(Auth::User()->id, 'appointments', 'created_from')
                    );
                }
            }
        }
        if ($request->get('created_to') && $request->get('created_to') != '') {
            $where[] = array(
                'appointments.created_at',
                '<=',
                $request->get('created_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, 'appointments', 'created_to', $request->get('created_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'created_to')) {
                    $where[] = array(
                        'appointments.created_at',
                        '<=',
                        Filters::get(Auth::User()->id, 'appointments', 'created_to')
                    );
                }
            }
        }
        if ($request->get('created_by') && count(array_filter($request->get('created_by'))) != 0) {
            $where[] = [
                'appointments.created_by',
                'in',
                $request->get('created_by')
            ];
            Filters::put(Auth::User()->id, 'appointments', 'created_by', $request->get('created_by'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'created_by');
            } else {
                $savedCreatedBy = Filters::get(Auth::User()->id, 'appointments', 'created_by');
                if (!empty($savedCreatedBy)) {
                    $where[] = [
                        'appointments.created_by',
                        'in',
                        $savedCreatedBy
                    ];
                }
            }
        }
        if ($request->get('converted_by') && count(array_filter($request->get('converted_by'))) != 0) {
            $where[] = [
                'appointments.converted_by',
                'in',
                $request->get('converted_by')
            ];
            Filters::put(Auth::User()->id, 'appointments', 'converted_by', $request->get('converted_by'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'converted_by');
            } else {
                $savedConvertedBy = Filters::get(Auth::User()->id, 'appointments', 'converted_by');
                if (!empty($savedConvertedBy)) {
                    $where[] = [
                        'appointments.converted_by',
                        'in',
                        $savedConvertedBy
                    ];
                }
            }
        }
        if ($request->get('updated_by') && count(array_filter($request->get('updated_by'))) != 0) {
            $where[] = [
                'appointments.updated_by',
                'in',
                $request->get('updated_by')
            ];
            Filters::put(Auth::User()->id, 'appointments', 'updated_by', $request->get('updated_by'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'updated_by');
            } else {
                $savedUpdatedBy = Filters::get(Auth::User()->id, 'appointments', 'updated_by');
                if (!empty($savedUpdatedBy)) {
                    $where[] = [
                        'appointments.updated_by',
                        'in',
                        $savedUpdatedBy
                    ];
                }
            }
        }
        if ($request->get('source') && $request->get('source') != '') {
            $where[] = array(
                'appointments.source',
                '=',
                $request->get('source')
            );
            Filters::put(Auth::User()->id, 'appointments', 'source', $request->get('source'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'source');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'source')) {
                    $where[] = array(
                        'appointments.id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'source')
                    );
                }
            }
        }

        $appointment_cancel_status = AppointmentStatuses::where('is_cancelled', '=', '1')->first();

        if (count($where)) {
            $check = false;
            foreach ($where as $wh) {
                if ($wh[0] == 'appointments.base_appointment_status_id') {
                    $check = true;
                }
            }
            if (!$check) {
                $where[] = array(
                    'appointments.base_appointment_status_id',
                    '!=',
                    $appointment_cancel_status->id
                );
            }
        } else {
            $where[] = array(
                'appointments.base_appointment_status_id',
                '!=',
                $appointment_cancel_status->id
            );
        }

        return $where;
    }

    /*
     * Get the filters for count
     */
    static function appointments_filters_count($request, $account_id, $apply_filter)
    {
        $where_user = array();
        $where_lead = array();
        $where_appointment = array();

        $where_user[] = array(
            'user_type_id',
            '=',
            config('constants.patient_id')
        );

        if ($request->get('patient_id') && $request->get('patient_id') != '') {
            $where_user[] = array(
                'id',
                '=',
                $request->get('patient_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'patient_id', $request->get('patient_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'patient_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'patient_id')) {
                    $where_user[] = array(
                        'id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'patient_id')
                    );
                }
            }
        }
        if ($request->get('name') && $request->get('name') != '') {
            $where_user[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
            );
            Filters::put(Auth::User()->id, 'appointments', 'name', $request->get('name'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'name');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'name')) {
                    $where_user[] = array(
                        'name',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'name')
                    );
                }
            }
        }
        if ($request->get('phone') && $request->get('phone') != '') {
            $where_user[] = array(
                'phone',
                'like',
                '%' . GeneralFunctions::cleanNumber($request->get('phone')) . '%'
            );
            Filters::put(Auth::User()->id, 'appointments', 'phone', $request->get('phone'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'phone');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'phone')) {
                    $where_user[] = array(
                        'phone',
                        'like',
                        '%' . GeneralFunctions::cleanNumber(Filters::get(Auth::User()->id, 'appointments', 'phone')) . '%'
                    );
                }
            }
        }
        if ($request->get('date_from') && $request->get('date_from') != '') {
            $where_appointment[] = array(
                'scheduled_date',
                '>=',
                $request->get('date_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, 'appointments', 'date_from', $request->get('date_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'date_from');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'date_from')) {
                    $where_appointment[] = array(
                        'scheduled_date',
                        '>=',
                        Filters::get(Auth::User()->id, 'appointments', 'date_from')
                    );
                }
            }
        }
        if ($request->get('date_to') && $request->get('date_to') != '') {
            $where_appointment[] = array(
                'scheduled_date',
                '<=',
                $request->get('date_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, 'appointments', 'date_to', $request->get('date_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'date_to');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'date_to')) {
                    $where_appointment[] = array(
                        'scheduled_date',
                        '<=',
                        Filters::get(Auth::User()->id, 'appointments', 'date_to')
                    );
                }
            }
        }
        if ($request->get('doctor_id') && $request->get('doctor_id') != '') {
            $where_appointment[] = array(
                'doctor_id',
                '=',
                $request->get('doctor_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'doctor_id', $request->get('doctor_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'doctor_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'doctor_id')) {
                    $where_appointment[] = array(
                        'doctor_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'doctor_id')
                    );
                }
            }
        }
        if ($request->get('region_id') && $request->get('region_id') != '') {
            $where_appointment[] = array(
                'region_id',
                '=',
                $request->get('region_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'region_id', $request->get('region_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'region_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'region_id')) {
                    $where_appointment[] = array(
                        'region_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'region_id')
                    );
                }
            }
        }
        if ($request->get('city_id') && $request->get('city_id') != '') {
            $where_appointment[] = array(
                'city_id',
                '=',
                $request->get('city_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'city_id', $request->get('city_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'city_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'city_id')) {
                    $where_appointment[] = array(
                        'city_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'city_id')
                    );
                }
            }
        }
        if ($request->get('town_id') && $request->get('town_id') != '') {
            $where_lead[] = array(
                'town_id',
                '=',
                $request->get('town_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'town_id', $request->get('town_id'));
        } else {
            if ($apply_filter) {

                Filters::forget(Auth::User()->id, 'appointments', 'town_id');
            } else {

                if (Filters::get(Auth::User()->id, 'appointments', 'town_id')) {
                    $where_lead[] = array(
                        'town_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'town_id')
                    );
                }
            }
        }
        if ($request->get('location_id') && $request->get('location_id') != '') {
            $where_appointment[] = array(
                'location_id',
                '=',
                $request->get('location_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'location_id', $request->get('location_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'location_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'location_id')) {
                    $where_appointment[] = array(
                        'location_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'location_id')
                    );
                }
            }
        }
        if ($request->get('lead_source_id') && $request->get('lead_source_id') != '') {
            $where_lead[] = array(
                'lead_source_id',
                '=',
                $request->get('lead_source_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'lead_source_id', $request->get('lead_source_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'lead_source_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'lead_source_id')) {
                    $where_lead[] = array(
                        'lead_source_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'lead_source_id')
                    );
                }
            }
        }
        if ($request->get('service_id') && $request->get('service_id') != '') {
            $where_appointment[] = array(
                'service_id',
                '=',
                $request->get('service_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'service_id', $request->get('service_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'service_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'service_id')) {
                    $where_appointment[] = array(
                        'service_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'service_id')
                    );
                }
            }
        }
        if ($request->get('appointment_status_id') && $request->get('appointment_status_id') != '') {
            $where_appointment[] = array(
                'base_appointment_status_id',
                '=',
                $request->get('appointment_status_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'appointment_status_id', $request->get('appointment_status_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'appointment_status_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'appointment_status_id')) {
                    $where_appointment[] = array(
                        'base_appointment_status_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'appointment_status_id')
                    );
                }
            }
        }
        if ($request->get('buisness_status_id') && $request->get('buisness_status_id') != '') {
            $where_appointment[] = array(
                'buisness_status_id',
                '=',
                $request->get('buisness_status_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'buisness_status_id', $request->get('buisness_status_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'buisness_status_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'buisness_status_id')) {
                    $where_appointment[] = array(
                        'buisness_status_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'buisness_status_id')
                    );
                }
            }
        }
        if ($request->get('appointment_type_id') && $request->get('appointment_type_id') != '') {
            $where_appointment[] = array(
                'appointment_type_id',
                '=',
                $request->get('appointment_type_id')
            );
            Filters::put(Auth::User()->id, 'appointments', 'appointment_type_id', $request->get('appointment_type_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'appointment_type_id');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'appointment_type_id')) {
                    $where_appointment[] = array(
                        'appointments.appointment_type_id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'appointment_type_id')
                    );
                }
            }
        }
        if ($request->get('consultancy_type') && $request->get('consultancy_type') != '') {
            $where_appointment[] = array(
                'consultancy_type',
                '=',
                $request->get('consultancy_type')
            );
            Filters::put(Auth::User()->id, 'appointments', 'consultancy_type', $request->get('consultancy_type'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'consultancy_type');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'consultancy_type')) {
                    $where_appointment[] = array(
                        'consultancy_type',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'consultancy_type')
                    );
                }
            }
        }
        if ($request->get('created_from') && $request->get('created_from') != '') {
            $where_appointment[] = array(
                'created_at',
                '>=',
                $request->get('created_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, 'appointments', 'created_from', $request->get('created_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'created_from')) {
                    $where_appointment[] = array(
                        'created_at',
                        '>=',
                        Filters::get(Auth::User()->id, 'appointments', 'created_from')
                    );
                }
            }
        }
        if ($request->get('created_to') && $request->get('created_to') != '') {
            $where_appointment[] = array(
                'created_at',
                '<=',
                $request->get('created_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, 'appointments', 'created_to', $request->get('created_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'created_to')) {
                    $where_appointment[] = array(
                        'created_at',
                        '<=',
                        Filters::get(Auth::User()->id, 'appointments', 'created_to')
                    );
                }
            }
        }
        if ($request->get('created_by') && count(array_filter($request->get('created_by'))) != 0) {
            $where_appointment[] = [
                'created_by',
                'in',
                $request->get('created_by')
            ];
            Filters::put(Auth::User()->id, 'appointments', 'created_by', $request->get('created_by'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'created_by');
            } else {
                $savedCreatedBy = Filters::get(Auth::User()->id, 'appointments', 'created_by');
                if (!empty($savedCreatedBy)) {
                    $where_appointment[] = [
                        'created_by',
                        'in',
                        $savedCreatedBy
                    ];
                }
            }
        }
        if ($request->get('converted_by') && count(array_filter($request->get('converted_by'))) != 0) {
            $where_appointment[] = [
                'converted_by',
                'in',
                $request->get('converted_by')
            ];
            Filters::put(Auth::User()->id, 'appointments', 'converted_by', $request->get('converted_by'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'converted_by');
            } else {
                $savedConvertedBy = Filters::get(Auth::User()->id, 'appointments', 'converted_by');
                if (!empty($savedConvertedBy)) {
                    $where_appointment[] = [
                        'converted_by',
                        'in',
                        $savedConvertedBy
                    ];
                }
            }
        }
        if ($request->get('updated_by') && count(array_filter($request->get('updated_by'))) != 0) {
            $where_appointment[] = [
                'updated_by',
                'in',
                $request->get('updated_by')
            ];
            Filters::put(Auth::User()->id, 'appointments', 'updated_by', $request->get('updated_by'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'updated_by');
            } else {
                $savedUpdatedBy = Filters::get(Auth::User()->id, 'appointments', 'updated_by');
                if (!empty($savedUpdatedBy)) {
                    $where_appointment[] = [
                        'updated_by',
                        'in',
                        $savedUpdatedBy
                    ];
                }
            }
        }
        if ($request->get('source') && $request->get('source') != '') {
            $where_appointment[] = array(
                'source',
                '=',
                $request->get('source')
            );
            Filters::put(Auth::User()->id, 'appointments', 'source', $request->get('source'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'appointments', 'source');
            } else {
                if (Filters::get(Auth::User()->id, 'appointments', 'source')) {
                    $where_appointment[] = array(
                        'id',
                        '=',
                        Filters::get(Auth::User()->id, 'appointments', 'source')
                    );
                }
            }
        }

        $appointment_cancel_status = AppointmentStatuses::where('is_cancelled', '=', '1')->first();

        if (count($where_appointment)) {
            $check = false;
            foreach ($where_appointment as $wh) {
                if ($wh[0] == 'base_appointment_status_id') {
                    $check = true;
                }
            }
            if (!$check) {
                $where_appointment[] = array(
                    'base_appointment_status_id',
                    '!=',
                    $appointment_cancel_status->id
                );
            }
        } else {
            $where_appointment[] = array(
                'base_appointment_status_id',
                '!=',
                $appointment_cancel_status->id
            );
        }

        return array($where_user, $where_lead, $where_appointment);
    }

    /**
     * Define the function to store the appointment reschedule information
     */
    public static function appointmentReschedule($appointment_id, $user_id, $scheduled_date, $scheduled_time)
    {

        $appointment_reschedule = array();
        $appointment_reschedule['appointment_id'] = $appointment_id;
        $appointment_reschedule['user_id'] = $user_id;
        $appointment_reschedule['scheduled_date'] = Carbon::parse($scheduled_date)->format("Y-m-d");
        $appointment_reschedule['scheduled_time'] = Carbon::parse($scheduled_time)->format("H:i:s");

        AppointmentReschedule::create($appointment_reschedule);

        return true;
    }

    /* this function is used in api for getting data for qr verification */
    public static function displayInvoiceAppointment_api($id)
    {

        $Invoiceinfo = DB::table('invoices')
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->where('invoices.id', '=', $id)
            ->select(
                'invoices.*',
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

        return response()->json([
            'status' => true,
            'message' => "Invoice Detail found",
            'Invoiceinfo' => $Invoiceinfo,
            'patient' => $patient,
            'service' => $service,
            'invoicestatus' => $invoicestatus,
            'location_info' => $location_info,
            'status_code' => 200,
        ]);
    }
}
