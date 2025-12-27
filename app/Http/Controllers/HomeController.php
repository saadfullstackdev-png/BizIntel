<?php

namespace App\Http\Controllers;

use App\Helpers\ACL;
use App\Http\Requests;
use App\Jobs\SyncAppointmentsJob;
use App\Models\Accounts;
use App\Models\AppointmentStatuses;
use App\Models\AppointmentTypes;
use App\Models\Invoices;
use App\Models\Patients;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Appointments;
use App\Models\Cities;
use App\Models\Doctors;
use App\Models\Leads;
use App\Models\Locations;
use App\Models\Services;
use App\User;
use Gate;
use Session;
use DB;
use Auth;
use Config;
use App\Reports\dashboardreport;
use App\Models\InvoiceStatuses;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $report = array();

        if (Gate::allows('users_manage')) {
            $report['users'] = User::getAllSystemUsersActiveRecords(Auth::User()->account_id)->count();
        }
        if (Gate::allows('leads_manage') || Gate::allows('leads_view')) {
            $report['leads'] = Leads::where(function ($query) {
                $query->whereIn('leads.city_id', ACL::getUserCities());
                $query->orWhereNull('leads.city_id');
            })
                ->count();

            $report['recent_leads'] = Leads::join('users', 'users.id', '=', 'leads.patient_id')
                ->where('users.user_type_id', '=', Config::get('constants.patient_id'))
                ->where(function ($query) {
                    $query->whereIn('leads.city_id', ACL::getUserCities());
                    $query->orWhereNull('leads.city_id');
                })
                ->select('*', 'leads.created_by as lead_created_by', 'leads.id as lead_id', 'leads.created_at as lead_created_at', 'users.id as PatientId')
                ->limit(10)
                ->orderBy('leads.created_at', 'asc')
                ->get();

//            dd($report['recent_leads']);
        }
        if (Gate::allows('appointments_manage') || Gate::allows('appointments_view')) {
            $report['appointments'] = Appointments::whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres())
                ->count();

            $report['recent_appointments'] = Appointments::join('users', function ($join) {
                $join->on('users.id', '=', 'appointments.patient_id')
                    ->where('users.user_type_id', '=', config('constants.patient_id'));
            })
                ->whereIn('appointments.city_id', ACL::getUserCities())
                ->whereIn('appointments.location_id', ACL::getUserCentres())
                ->select('*', 'appointments.name as patient_name', 'appointments.id as app_id', 'appointments.created_by as app_created_by', 'appointments.created_at as app_created_at')
                ->limit(10)
                ->orderBy('appointments.created_at', 'desc')
                ->get();
        }
        return view('home', compact('report'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function elastic()
    {
        $this->dispatch(
            new SyncAppointmentsJob(Accounts::find(Auth::User()->account_id))
        );

        flash('Elastic Sync Data Event is dispatched.')->success()->important();
        return redirect()->route('admin.home');
    }

    /*
     * Get Centres Revenue
     *
     * @oaran \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function revenueByCentre(Request $request)
    {
        $data = array(
            'today' => array(),

        );

        if (Gate::allows('dashboard_revenue_by_centre') || Gate::allows('dashboard_my_revenue_by_centre')) {

            $locations = Locations::where([
                ['account_id', '=', session('account_id')],
                ['active', '=', '1']
            ])->get();
            $invoicestatus = InvoiceStatuses::where('slug', '=', 'paid')->first();

            switch ($request->get('period')) {
                case 'today':
                    $start_date = Carbon::now()->format('Y-m-d');
                    $end_date = Carbon::now()->format('Y-m-d');
                    break;

                case 'yesterday':
                    $start_date = Carbon::now()->subDay(1)->format('Y-m-d');
                    $end_date = Carbon::now()->subDay(1)->format('Y-m-d');
                    break;

                case 'last7days':
                    $start_date = Carbon::now()->subDay(6)->format('Y-m-d');
                    $end_date = Carbon::now()->format('Y-m-d');
                    break;

                case 'thismonth':
                    $start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $end_date = Carbon::now()->endOfMonth()->format('Y-m-d');
                    break;
            }


            $todayRecords = \App\Models\Invoices::whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->whereIn('location_id', ACL::getUserCentres())
                ->where('invoice_status_id', '=', $invoicestatus->id);

            if ($request->get('performance') == '1') {
                $todayRecords = $todayRecords->where('created_by', '=', Auth::User()->id);
            }

            $todayRecords = $todayRecords->select('location_id', DB::raw("SUM(invoices.total_price) AS total_price"))
                ->groupBy('location_id')
                ->get();

            $today = array();
            if ($locations) {
                foreach ($locations as $location) {
                    $today[$location->id] = array(
                        'centre' => $location->city->name . ' - ' . $location->name,
                        'value' => 0.00,
                    );
                    if ($todayRecords) {
                        foreach ($todayRecords as $todayRecord) {
                            if ($todayRecord->location_id == $location->id) {
                                $today[$location->id]['value'] = $todayRecord->total_price;
                            }
                        }
                    }
                }
            }
            if (count($today)) {
                foreach ($today as $record) {
                    $data['today'][] = $record;
                }
            }

            return response()->json($data);
        }
    }

    /*
         * Get Centres Collection
         *
         * @oaran \Illuminate\Http\Request $request
         * @return \Illuminate\Http\Response
         */
    public function collectionByCentre(Request $request)
    {
        $data = array(
            'today' => array(),
            'yesterday' => array(),
            'last7days' => array(),
            'thismonth' => array(),
        );

        if (Gate::allows('dashboard_collection_by_centre') || Gate::allows('dashboard_my_collection_by_centre')) {

            $location_information = Locations::getActiveSorted(ACL::getUserCentres());

            if ($request->get('today') != '') {
                $todayRecords = dashboardreport::collectionbyrevenuewidgets($location_information, Auth::User()->account_id, 'today', $request);
                if (count($todayRecords)) {
                    foreach ($todayRecords as $record) {
                        $data['today'][] = $record;
                    }
                }
            }
            if ($request->get('yesterday') != '') {
                $yesterdayRecords = dashboardreport::collectionbyrevenuewidgets($location_information, Auth::User()->account_id, 'yesterday', $request);
                if (count($yesterdayRecords)) {
                    foreach ($yesterdayRecords as $record) {
                        $data['yesterday'][] = $record;
                    }
                }
            }
            if ($request->get('last7days') != '') {
                $last7dayRecords = dashboardreport::collectionbyrevenuewidgets($location_information, Auth::User()->account_id, 'last7day', $request);
                if (count($last7dayRecords)) {
                    foreach ($last7dayRecords as $record) {
                        $data['last7days'][] = $record;
                    }
                }
            }
            if ($request->get('thismonth') != '') {
                $thisMonthRecords = dashboardreport::collectionbyrevenuewidgets($location_information, Auth::User()->account_id, 'thisMonth', $request);
                if (count($thisMonthRecords)) {
                    foreach ($thisMonthRecords as $record) {
                        $data['thismonth'][] = $record;
                    }
                }
            }
        }

        return response()->json($data);
    }

    /*
     * Get Centres Revenue
     *
     * @oaran \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function revenueByService(Request $request)
    {
        $data = array(
            'today' => array(),
            'yesterday' => array(),
            'last7days' => array(),
            'thismonth' => array(),
        );

        if (Gate::allows('dashboard_revenue_by_service') || Gate::allows('dashboard_my_revenue_by_service')) {

            $services = Services::where([
                ['account_id', '=', session('account_id')],
                ['active', '=', '1']
            ])->get();

            $invoicestatus = InvoiceStatuses::where('slug', '=', 'paid')->first();
            if ($request->get('today') != '') {
                $todayRecords = Invoices::join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->whereDate('invoices.created_at', '=', Carbon::now()->format('Y-m-d'))
                    ->where('invoices.invoice_status_id', '=', $invoicestatus->id)
                    ->whereIn('invoices.location_id', ACL::getUserCentres());

                if ($request->get('performance')) {
                    $todayRecords->where('invoices.created_by', Auth::User()->id);
                }

                $todayRecords = $todayRecords->select('invoice_details.service_id', DB::raw("SUM(invoices.total_price) AS total_price"))
                    ->groupBy('invoice_details.service_id')
                    ->get();

                $today = array();
                if ($services) {
                    foreach ($services as $service) {
                        $today[$service->id] = array(
                            'centre' => $service->name,
                            'value' => 0.00,
                        );
                        if ($todayRecords) {
                            foreach ($todayRecords as $todayRecord) {
                                if ($todayRecord->service_id == $service->id) {
                                    $today[$service->id]['value'] = $todayRecord->total_price;
                                }
                            }
                        }
                    }
                }
                if (count($today)) {
                    foreach ($today as $record) {
                        $data['today'][] = $record;
                    }
                }
            }

            if ($request->get('yesterday') != '') {
                $yesterdayRecords = Invoices::join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->whereDate('invoices.created_at', '>=', Carbon::now()->subDay(1)->format('Y-m-d'))
                    ->whereDate('invoices.created_at', '<=', Carbon::now()->subDay(1)->format('Y-m-d'))
                    ->where('invoices.invoice_status_id', '=', $invoicestatus->id)
                    ->whereIn('invoices.location_id', ACL::getUserCentres());

                if ($request->get('performance')) {
                    $yesterdayRecords->where('invoices.created_by', Auth::User()->id);
                }

                $yesterdayRecords = $yesterdayRecords->select('invoice_details.service_id', DB::raw("SUM(invoices.total_price) AS total_price"))
                    ->groupBy('invoice_details.service_id')
                    ->get();

                $yesterday = array();
                if ($services) {
                    foreach ($services as $service) {
                        $yesterday[$service->id] = array(
                            'centre' => $service->name,
                            'value' => 0.00,
                        );
                        if ($yesterdayRecords) {
                            foreach ($yesterdayRecords as $yesterdayRecord) {
                                if ($yesterdayRecord->service_id == $service->id) {
                                    $yesterday[$service->id]['value'] = $yesterdayRecord->total_price;
                                }
                            }
                        }
                    }
                }
                if (count($yesterday)) {
                    foreach ($yesterday as $record) {
                        $data['yesterday'][] = $record;
                    }
                }

            }
            if ($request->get('last7days') != '') {

                $last7DaysRecords = Invoices::join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->whereDate('invoices.created_at', '>=', Carbon::now()->subDay(6)->format('Y-m-d'))
                    ->whereDate('invoices.created_at', '<=', Carbon::now()->format('Y-m-d'))
                    ->where('invoices.invoice_status_id', '=', $invoicestatus->id)
                    ->whereIn('invoices.location_id', ACL::getUserCentres());

                if ($request->get('performance')) {
                    $last7DaysRecords = $last7DaysRecords->where('invoices.created_by', Auth::User()->id);
                }

                $last7DaysRecords = $last7DaysRecords->select('invoice_details.service_id', DB::raw("SUM(invoices.total_price) AS total_price"))
                    ->groupBy('invoice_details.service_id')
                    ->get();

                $last7days = array();
                if ($services) {
                    foreach ($services as $service) {
                        $last7days[$service->id] = array(
                            'centre' => $service->name,
                            'value' => 0.00,
                        );
                        if ($last7DaysRecords) {
                            foreach ($last7DaysRecords as $last7DaysRecord) {
                                if ($last7DaysRecord->service_id == $service->id) {
                                    $last7days[$service->id]['value'] = $last7DaysRecord->total_price;
                                }
                            }
                        }
                    }
                }
                if (count($last7days)) {
                    foreach ($last7days as $record) {
                        $data['last7days'][] = $record;
                    }
                }
            }
            if ($request->get('thismonth') != '') {
                $thisMonthRecords = Invoices::join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->whereDate('invoices.created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))
                    ->whereDate('invoices.created_at', '<=', Carbon::now()->endOfMonth()->format('Y-m-d'))
                    ->where('invoices.invoice_status_id', '=', $invoicestatus->id)
                    ->whereIn('invoices.location_id', ACL::getUserCentres());

                if ($request->get('performance')) {
                    $thisMonthRecords = $thisMonthRecords->where('invoices.created_by', Auth::User()->id);
                }

                $thisMonthRecords = $thisMonthRecords->select('invoice_details.service_id', DB::raw("SUM(invoices.total_price) AS total_price"))
                    ->groupBy('invoice_details.service_id')
                    ->get();

                $thisMonth = array();
                if ($services) {
                    foreach ($services as $service) {
                        $thisMonth[$service->id] = array(
                            'centre' => $service->name,
                            'value' => 0.00,
                        );
                        if ($thisMonthRecords) {
                            foreach ($thisMonthRecords as $thisMonthRecord) {
                                if ($thisMonthRecord->service_id == $service->id) {
                                    $thisMonth[$service->id]['value'] = $thisMonthRecord->total_price;
                                }
                            }
                        }
                    }
                }
                if (count($thisMonth)) {
                    foreach ($thisMonth as $record) {
                        $data['thismonth'][] = $record;
                    }
                }
            }
        }
        //dd($data);
        return response()->json($data);
    }

    /*
     * Get Appointments by status
     *
     * @oaran \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function appointmentByStatus(Request $request)
    {

        $data = array(
            'today' => array(),
        );

        if (Gate::allows('dashboard_appointment_by_status') || Gate::allows('dashboard_my_appointment_by_status')) {
            $appointment_statuses = AppointmentStatuses::where([
                ['account_id', '=', session('account_id')],
                ['active', '=', '1'],
                ['parent_id', '=', '0'],
            ])->get();

            switch ($request->get('period')) {
                case 'today':
                    $start_date = Carbon::now()->format('Y-m-d');
                    $end_date = Carbon::now()->format('Y-m-d');
                    break;

                case 'yesterday':
                    $start_date = Carbon::now()->subDay(1)->format('Y-m-d');
                    $end_date = Carbon::now()->subDay(1)->format('Y-m-d');
                    break;

                case 'last7days':
                    $start_date = Carbon::now()->subDay(6)->format('Y-m-d');
                    $end_date = Carbon::now()->format('Y-m-d');
                    break;

                case 'thismonth':
                    $start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $end_date = Carbon::now()->endOfMonth()->format('Y-m-d');
                    break;
            }


            $todayRecords = Appointments::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->whereIn('location_id', ACL::getUserCentres());

            if ($request->get('performance')) {
                $todayRecords = $todayRecords->where('created_by', Auth::User()->id);
            }

            $todayRecords = $todayRecords->select('base_appointment_status_id as appointment_status_id', DB::raw("COUNT(id) AS total"))
                ->groupBy('base_appointment_status_id')
                ->get();

            $today = array();
            if ($appointment_statuses) {
                foreach ($appointment_statuses as $appointment_status) {
                    $today[$appointment_status->id] = array(
                        'appointment' => $appointment_status->name,
                        'value' => 0,
                    );
                    if ($todayRecords) {
                        foreach ($todayRecords as $todayRecord) {
                            if ($todayRecord->appointment_status_id == $appointment_status->id) {
                                $today[$appointment_status->id]['value'] = $todayRecord->total;
                            }
                        }
                    }
                }
            }
            if (count($today)) {
                foreach ($today as $record) {
                    $data['today'][] = $record;
                }
            }

            return response()->json($data);
        }
    }

    /*
        * Get Appointments by Type
        *
        * @oaran \Illuminate\Http\Request $request
        * @return \Illuminate\Http\Response
        */
    public function appointmentByType(Request $request)
    {
        $data = array(
            'today' => array(),
            'yesterday' => array(),
            'last7days' => array(),
            'thismonth' => array(),
        );

        if (Gate::allows('dashboard_appointment_by_type') || Gate::allows('dashboard_my_appointment_by_type')) {

            $appointment_types = AppointmentTypes::where([
                ['account_id', '=', session('account_id')],
                ['active', '=', '1'],
            ])->get();

            if ($request->get('today') != '') {
                $todayRecords = Appointments::whereDate('created_at', '=', Carbon::now()->format('Y-m-d'))
                    ->whereIn('location_id', ACL::getUserCentres());

                if ($request->get('performance')) {
                    $todayRecords = $todayRecords->where('created_by', Auth::User()->id);
                }

                $todayRecords = $todayRecords->select('appointment_type_id', DB::raw("COUNT(id) AS total"))
                    ->groupBy('appointment_type_id')
                    ->get();

                $today = array();
                if ($appointment_types) {
                    foreach ($appointment_types as $appointment_status) {
                        $today[$appointment_status->id] = array(
                            'appointment' => $appointment_status->name,
                            'value' => 0,
                        );
                        if ($todayRecords) {
                            foreach ($todayRecords as $todayRecord) {
                                if ($todayRecord->appointment_type_id == $appointment_status->id) {
                                    $today[$appointment_status->id]['value'] = $todayRecord->total;
                                }
                            }
                        }
                    }
                }
                if (count($today)) {
                    foreach ($today as $record) {
                        $data['today'][] = $record;
                    }
                }
            }
            if ($request->get('yesterday') != '') {
                $yesterdayRecords = Appointments::whereDate('created_at', '>=', Carbon::now()->subDay(1)->format('Y-m-d'))
                    ->whereDate('created_at', '<=', Carbon::now()->subDay(1)->format('Y-m-d'))
                    ->whereIn('location_id', ACL::getUserCentres());

                if ($request->get('performance')) {
                    $yesterdayRecords = $yesterdayRecords->where('created_by', Auth::User()->id);
                }

                $yesterdayRecords = $yesterdayRecords->select('appointment_type_id', DB::raw("COUNT(id) AS total"))
                    ->groupBy('appointment_type_id')
                    ->get();

                $yesterday = array();
                if ($appointment_types) {
                    foreach ($appointment_types as $appointment_status) {
                        $yesterday[$appointment_status->id] = array(
                            'appointment' => $appointment_status->name,
                            'value' => 0,
                        );
                        if ($yesterdayRecords) {
                            foreach ($yesterdayRecords as $yesterdayRecord) {
                                if ($yesterdayRecord->appointment_type_id == $appointment_status->id) {
                                    $yesterday[$appointment_status->id]['value'] = $yesterdayRecord->total;
                                }
                            }
                        }
                    }
                }
                if (count($yesterday)) {
                    foreach ($yesterday as $record) {
                        $data['yesterday'][] = $record;
                    }
                }
            }
            if ($request->get('last7days') != '') {
                $last7DaysRecords = Appointments::whereDate('created_at', '>=', Carbon::now()->subDay(6)->format('Y-m-d'))
                    ->whereDate('created_at', '<=', Carbon::now()->format('Y-m-d'))
                    ->whereIn('location_id', ACL::getUserCentres());

                if ($request->get('performance')) {
                    $last7DaysRecords = $last7DaysRecords->where('created_by', Auth::User()->id);
                }

                $last7DaysRecords = $last7DaysRecords->select('appointment_type_id', DB::raw("COUNT(id) AS total"))
                    ->groupBy('appointment_type_id')
                    ->get();

                $last7days = array();
                if ($appointment_types) {
                    foreach ($appointment_types as $appointment_status) {
                        $last7days[$appointment_status->id] = array(
                            'appointment' => $appointment_status->name,
                            'value' => 0,
                        );
                        if ($last7DaysRecords) {
                            foreach ($last7DaysRecords as $last7DaysRecord) {
                                if ($last7DaysRecord->appointment_type_id == $appointment_status->id) {
                                    $last7days[$appointment_status->id]['value'] = $last7DaysRecord->total;
                                }
                            }
                        }
                    }
                }
                if (count($last7days)) {
                    foreach ($last7days as $record) {
                        $data['last7days'][] = $record;
                    }
                }
            }
            if ($request->get('thismonth') != '') {
                $thisMonthRecords = Appointments::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))
                    ->whereDate('created_at', '<=', Carbon::now()->endOfMonth()->format('Y-m-d'))
                    ->whereIn('location_id', ACL::getUserCentres());

                if ($request->get('performance')) {
                    $thisMonthRecords = $thisMonthRecords->where('created_by', Auth::User()->id);
                }

                $thisMonthRecords = $thisMonthRecords->select('appointment_type_id', DB::raw("COUNT(id) AS total"))
                    ->groupBy('appointment_type_id')
                    ->get();

                $thisMonth = array();
                if ($appointment_types) {
                    foreach ($appointment_types as $appointment_status) {
                        $thisMonth[$appointment_status->id] = array(
                            'appointment' => $appointment_status->name,
                            'value' => 0,
                        );
                        if ($thisMonthRecords) {
                            foreach ($thisMonthRecords as $thisMonthRecord) {
                                if ($thisMonthRecord->appointment_type_id == $appointment_status->id) {
                                    $thisMonth[$appointment_status->id]['value'] = $thisMonthRecord->total;
                                }
                            }
                        }
                    }
                }
                if (count($thisMonth)) {
                    foreach ($thisMonth as $record) {
                        $data['thismonth'][] = $record;
                    }
                }
            }
        }

        return response()->json($data);
    }
}
