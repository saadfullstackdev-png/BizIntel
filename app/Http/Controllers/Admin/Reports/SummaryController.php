<?php

namespace App\Http\Controllers\Admin\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Helpers\ACL;
use App\Helpers\NodesTree;
use App\Models\AppointmentStatuses;
use App\Models\AppointmentTypes;
use App\Models\Cities;
use App\Models\Doctors;
use App\Models\Invoices;
use App\Models\Leads;
use App\Models\LeadSources;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\Patients;
use App\Models\Regions;
use App\Models\Services;
use App\Models\Appointments;
use App\Models\DoctorHasLocations;
use App\Reports\Finanaces;
use App\User;
use Auth;
use Carbon\Carbon;
use Excel;
use Barryvdh\DomPDF\Facade as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App;
use App\Helpers\dateType;



class SummaryController extends Controller
{
    public function report()
    {
        // if (!Gate::allows('appointment_reports_manage')) {
        //     return abort(401);
        // }

        $allserviceslug = Services::where('slug', '=', 'all')->first();

        $cities = Cities::getActiveSortedFeatured(ACL::getUserCities());
        $cities->prepend('All', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        $doctors = Doctors::getActiveOnly(ACL::getUserCentres());
        $doctors->prepend('All', '');

        $locations = Locations::where('active', 1)->get();
        // $locations->prepend('All', '');

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);
        $services = $parentGroups->nodeList;

        foreach ($services as $key => $ser) {
            if ($key) {
                if ($ser['name'] == $allserviceslug->name) {
                    unset($services[$key]);
                }
            }
        }
        $appointment_statuses = AppointmentStatuses::getAllParentRecords(Auth::User()->account_id);
        if ($appointment_statuses) {
            $appointment_statuses = $appointment_statuses->pluck('name', 'id');
        }
        $appointment_statuses->prepend('All', '');

        $appointment_types = AppointmentTypes::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $appointment_types->prepend('All', '');

        $employees = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $operators = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array('' => 'All');

        $users = ($select_All + $employees->toArray() + $operators->toArray());

        $lead_sources = LeadSources::where('active', 1)->get();
        // $lead_sources->prepend('Select a Lead Source', '');
        

        return view('admin.reports.summary_report.index', compact('cities', 'lead_sources', 'regions', 'doctors', 'locations', 'services', 'appointment_statuses', 'appointment_types', 'users'));
    }

    public function reportlead()
    {
        // if (!Gate::allows('appointment_reports_manage')) {
        //     return abort(401);
        // }

        $allserviceslug = Services::where('slug', '=', 'all')->first();

        $cities = Cities::getActiveSortedFeatured(ACL::getUserCities());
        $cities->prepend('All', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        $doctors = Doctors::getActiveOnly(ACL::getUserCentres());
        $doctors->prepend('All', '');

        $locations = Locations::where('active', 1)->get();
        // $locations->prepend('All', '');

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);
        $services = $parentGroups->nodeList;

        foreach ($services as $key => $ser) {
            if ($key) {
                if ($ser['name'] == $allserviceslug->name) {
                    unset($services[$key]);
                }
            }
        }
        $appointment_statuses = AppointmentStatuses::getAllParentRecords(Auth::User()->account_id);
        if ($appointment_statuses) {
            $appointment_statuses = $appointment_statuses->pluck('name', 'id');
        }
        $appointment_statuses->prepend('All', '');

        $appointment_types = AppointmentTypes::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $appointment_types->prepend('All', '');

        $employees = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $operators = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array('' => 'All');

        $users = ($select_All + $employees->toArray() + $operators->toArray());

        $lead_sources = LeadSources::where('active', 1)->get();
        // $lead_sources->prepend('Select a Lead Source', '');
        

        return view('admin.reports.summary_report_lead.index', compact('cities', 'lead_sources', 'regions', 'doctors', 'locations', 'services', 'appointment_statuses', 'appointment_types', 'users'));
    }

    public function reportconversion()
    {
        // if (!Gate::allows('appointment_reports_manage')) {
        //     return abort(401);
        // }

        $allserviceslug = Services::where('slug', '=', 'all')->first();

        $cities = Cities::getActiveSortedFeatured(ACL::getUserCities());
        $cities->prepend('All', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        $doctors = Doctors::getActiveOnly(ACL::getUserCentres());
        $doctors->prepend('All', '');

        $locations = Locations::where('active', 1)->get();
        // $locations->prepend('All', '');

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);
        $services = $parentGroups->nodeList;

        foreach ($services as $key => $ser) {
            if ($key) {
                if ($ser['name'] == $allserviceslug->name) {
                    unset($services[$key]);
                }
            }
        }
        $appointment_statuses = AppointmentStatuses::getAllParentRecords(Auth::User()->account_id);
        if ($appointment_statuses) {
            $appointment_statuses = $appointment_statuses->pluck('name', 'id');
        }
        $appointment_statuses->prepend('All', '');

        $appointment_types = AppointmentTypes::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $appointment_types->prepend('All', '');

        $employees = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $operators = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array('' => 'All');

        $users = ($select_All + $employees->toArray() + $operators->toArray());

        $lead_sources = LeadSources::where('active', 1)->get();
        // $lead_sources->prepend('Select a Lead Source', '');
        

        return view('admin.reports.summary_report_conversion.index', compact('cities', 'lead_sources', 'regions', 'doctors', 'locations', 'services', 'appointment_statuses', 'appointment_types', 'users'));
    }

    /**
     * Load Report for frontend display
     */
    public function reportLoad(Request $request)
    {
        return self::summaryReport($request);
    }

    public function summaryReport(Request $request)
    {
        // dd($request->all());
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d 00:00:00', strtotime($date_range[0]));
            $end_date = date('Y-m-d 23:59:59', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }
        // dd($request->all());
        $city_id = $request->get('city_id');
        $patient_id = $request->get('patient_id');
        $doctor_id = $request->get('doctor_id');
        $region_id = $request->get('region_id');
        $service_id = $request->get('service_id');
        $appointment_status_id = $request->get('appointment_status_id');
        $appointment_type_id = $request->get('appointment_type_id');
        $consultancy_type = $request->get('consultancy_type');
        $user_id = $request->get('user_id');
        $re_user_id = $request->get('re_user_id');
        $referred_by = $request->get('referred_by');
        $is_converted = $request->get('is_converted');
        $up_user_id = $request->get('up_user_id');

        $locationIds = [];
        if (!empty($doctor_id)) {
            $locationIds = DoctorHasLocations::where('user_id', $doctor_id)
                ->pluck('location_id')
                ->toArray();
        }

        $leadsQuery = Leads::select('leads.*', 'locations.name as location_name', 'lead_sources.name as source_name', 'lead_statuses.name as status_name')
            ->leftJoin('locations', 'leads.location_id', '=', 'locations.id')
            ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->leftJoin('lead_statuses', 'leads.lead_status_id', '=', 'lead_statuses.id')
            // ->leftJoin('appointments', 'appointments.lead_id', '=', 'leads.id')
            ->where(function ($q) {
                $q->whereIn('leads.city_id', [1, 2, 3, 4, 6, 7, 14, 38, 57, 62])//is_featured=1 active=1
                      ->orWhereNull('leads.city_id');
            })
            ->where(function ($query) {
                $query->whereNotIn('leads.lead_source_id', [2, 9, 10, 13, 14, 15, 19, 24, 28])
                      ->orWhereNull('leads.lead_source_id');
            })
            ->whereNotIn('leads.lead_status_id', [5, 6, 7, 8])
            ->whereBetween('leads.created_at', [$start_date, $end_date]);
        if ($request->has('patient_id') && !empty($request->get('patient_id'))) {
            $leadsQuery->where('leads.patient_id', $request->get('patient_id'));
        }
        if ($request->has('region_id') && !empty($request->get('region_id'))) {
            $leadsQuery->where('leads.region_id', $request->get('region_id'));
        }
        if ($request->has('service_id') && !empty($request->get('service_id'))) {
            $leadsQuery->where('leads.service_id', $request->get('service_id'));
        }
        if ($request->has('user_id') && !empty($request->get('user_id'))) {
            $leadsQuery->where('leads.created_by', $request->get('user_id'));
        }
        if ($request->has('up_user_id') && !empty($request->get('up_user_id'))) {
            $leadsQuery->where('leads.updated_by', $request->get('up_user_id'));
        }
        if ($request->has('re_user_id') && !empty($request->get('re_user_id'))) {
            $leadsQuery->where('leads.converted_by', $request->get('re_user_id'));
        }
        // if ($request->has('referred_by')) {
        //     $leadsQuery->where('leads.referred_by', $request->get('referred_by'));
        // }
        if ($request->has('location_id') && !empty($request->get('location_id'))) {
            $leadsQuery->whereIn('leads.location_id', $request->get('location_id'));
        }
        if ($request->has('lead_source_id') && !empty($request->get('lead_source_id'))) {
            $leadsQuery->whereIn('leads.lead_source_id', $request->get('lead_source_id'));
        }
        if (!empty($city_id)) {
            $leadsQuery->where('locations.city_id', $city_id);
        }
        if (!empty($locationIds)) {
            $leadsQuery->whereIn('leads.location_id', $locationIds);
        }
        if (!empty($doctor_id)) {
            $leadsQuery->leftJoin('appointments', 'appointments.lead_id', '=', 'leads.id');
            $leadsQuery->where('appointments.doctor_id', $doctor_id);
        }
        $leads = $leadsQuery->get();

        $locationsQuery = Locations::query();
        if (!empty($city_id)) {
            $locationsQuery->where('city_id', $city_id);
        }
        $locations = $locationsQuery->get();
        $leadsWithoutLocation = $leadsQuery->whereNull('leads.location_id')->get()->groupBy('city_id');
        $groupedByCenter = $leads->groupBy('location_id');
        // dd($groupedByCenter);
        $leadSource = LeadSources::where('active', 1);
        if ($request->has('lead_source_id') && !empty($request->get('lead_source_id'))) {
            $leadSource->whereIn('id', $request->get('lead_source_id'));
        }
        $leadSources = $leadSource->get();
        $total_revenue_cash_in = 0;
        $total_revenue_card_in = 0;
        $total_revenue_bank_in = 0;
        $total_revenue_wallet_in = 0;
        $total_refund = 0;
        $total_revenue = 0;

        $summaryData = [];

        foreach ($groupedByCenter as $locationId => $leadsGroup) {
            $filteredRequest = $request->all();
            $filteredRequest['location_id'] = [$locationId];
            $report_data = Finanaces::generalrevenuereportsummary($filteredRequest, Auth::User()->account_id);
            // dd($leadsGroup->get());
            $totalLeads = $leadsGroup->count();

            $sourcesCount = [];
            foreach ($leadSources as $source) {
                $sourcesCount[$source->name] = $leadsGroup->where('lead_source_id', $source->id)->count();
            }
            $cityid = Locations::where('id', $locationId)->value('city_id');
            $cityName = Cities::where('id', $cityid)->value('name');
            $bookedQuery = Appointments::query()
            ->where('appointments.location_id', $locationId)
            ->where('appointment_type_id', 1)
            ->whereNotIn('appointments.appointment_status_id', [4]);

            if ($request->has('date_range_by') && $request->get('date_range_by') == 'scheduled_date') {
            $bookedQuery->whereBetween('appointments.scheduled_date', [$start_date, $end_date]);
            } else {
            $bookedQuery->whereBetween('appointments.created_at', [$start_date, $end_date]);
            }

            $filters = [
            'city_id' => 'appointments.city_id',
            'patient_id' => 'appointments.patient_id',
            'doctor_id' => 'appointments.doctor_id',
            'region_id' => 'appointments.region_id',
            'service_id' => 'appointments.service_id',
            'appointment_status_id' => 'appointments.appointment_status_id',
            'appointment_type_id' => 'appointments.appointment_type_id',
            'consultancy_type' => 'appointments.consultancy_type',
            'user_id' => 'appointments.created_by',
            're_user_id' => 'appointments.converted_by',
            'up_user_id' => 'appointments.updated_by',
            ];

            foreach ($filters as $requestKey => $dbColumn) {
                if ($request->has($requestKey) && !empty($request->get($requestKey))) {
                    $bookedQuery->where($dbColumn, $request->get($requestKey));
                }
            }

            $booked = $bookedQuery->count();

            $leadStatusArrived = AppointmentStatuses::where('is_arrived', 1)->first();

            $arrivedLeadsQuery = clone $bookedQuery;
            $arrivedLeads = $arrivedLeadsQuery
            ->where('appointments.appointment_status_id', 2)
            ->where('appointments.active', 1)
            ->count();

            $consultancyLeadsQuery = clone $bookedQuery;
            $consultancyLeads = $consultancyLeadsQuery
            ->where('appointments.appointment_status_id', 2)
            ->where('appointments.active', 1)
            ->count();

            $notArrivedLeads = $booked - $arrivedLeads;

            $convertedLeadsQuery = clone $bookedQuery;
            $convertedLeads = $convertedLeadsQuery
            ->where('appointments.is_converted', 1)
            ->where('appointments.appointment_status_id', 2)
            ->where('appointments.active', 1)
            ->count();
            $notConvertedLeads = $arrivedLeads - $convertedLeads;
            $convertedRevenue = Invoices::whereIn('appointment_id', Appointments::where('is_converted', 1)
            ->whereIn('lead_id', $leadsGroup->pluck('id'))->pluck('id'))
            ->where('invoice_status_id',3)
            ->count();

            $revenuepaid = Invoices::whereIn('appointment_id', Appointments::where('is_converted', 1)
            ->whereIn('lead_id', $leadsGroup->pluck('id'))->pluck('id'))
            ->where('invoice_status_id',3)
            ->sum('total_price');
            

            $conversionRatio = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
            $conversionRatio_1 = $totalLeads > 0 ? ($booked / $totalLeads) * 100 : 0;
            $conversionRatio_2 = $booked > 0 ? ($arrivedLeads / $booked) * 100 : 0;
            $conversionRatio_3 = $arrivedLeads > 0 ? ($convertedLeads / $arrivedLeads) * 100 : 0;
            $conversionToRevenue = $convertedLeads > 0 ? ($convertedRevenue / $convertedLeads) : 0;
            if ($leadsGroup->first()->location_name) {
                $summaryData[] = array_merge([
                    'city' => $cityName,
                    'center' => $leadsGroup->first()->location_name,
                    'center_id' => $leadsGroup->first()->location_id,
                    'total_leads' => $totalLeads,
                    'booked' => $booked,
                    'arrived' => $arrivedLeads,
                    'not_arrived' => $notArrivedLeads,
                    'consultancy' => $consultancyLeads,
                    'converted' => $convertedLeads,
                    'not_converted' => $notConvertedLeads,
                    'converted_revenue' => $convertedRevenue,
                    'conversion_ratio' => $conversionRatio,
                    'conversion_ratio_1' => $conversionRatio_1,
                    'conversion_ratio_2' => $conversionRatio_2,
                    'conversion_ratio_3' => $conversionRatio_3,
                    'conversion_to_revenue' => $conversionToRevenue,
                    'revenuepaid' => $revenuepaid,
                    'revenue_cash_in' => $report_data[$locationId]['revenue_cash_in'] ?? 0,
                    'revenue_card_in' => $report_data[$locationId]['revenue_card_in'] ?? 0,
                    'revenue_bank_in' => $report_data[$locationId]['revenue_bank_in'] ?? 0,
                    'revenue_wallet_in' => $report_data[$locationId]['revenue_wallet_in'] ?? 0,
                    'refund' => $report_data[$locationId]['refund_out'] ?? 0,
                    'revenue' => $report_data[$locationId]['in_hand'] ?? 0,
                ], $sourcesCount);
            }
        }
        foreach ($leadsWithoutLocation as $cityId => $leadsGroup) {
            $sourcesCount = [];
            foreach ($leadSources as $source) {
                $sourcesCount[$source->name] = $leadsGroup->where('lead_source_id', $source->id)->count();
            }
            $cityName = Cities::where('id', $cityId)->value('name') ?? 'Unknown City';
            $totalLeads = $leadsGroup->count();
            $bookedQuery = Appointments::query()
            ->where('appointments.location_id', $locationId)
            ->where('appointment_type_id', 1)
            ->whereNotIn('appointments.appointment_status_id', [4]);

            if ($request->has('date_range_by') && $request->get('date_range_by') == 'scheduled_date') {
            $bookedQuery->whereBetween('appointments.scheduled_date', [$start_date, $end_date]);
            } else {
            $bookedQuery->whereBetween('appointments.created_at', [$start_date, $end_date]);
            }

            $filters = [
            'city_id' => 'appointments.city_id',
            'patient_id' => 'appointments.patient_id',
            'doctor_id' => 'appointments.doctor_id',
            'region_id' => 'appointments.region_id',
            'service_id' => 'appointments.service_id',
            'appointment_status_id' => 'appointments.appointment_status_id',
            'appointment_type_id' => 'appointments.appointment_type_id',
            'consultancy_type' => 'appointments.consultancy_type',
            'user_id' => 'appointments.created_by',
            're_user_id' => 'appointments.converted_by',
            'up_user_id' => 'appointments.updated_by',
            ];

            foreach ($filters as $requestKey => $dbColumn) {
                if ($request->has($requestKey) && !empty($request->get($requestKey))) {
                    $bookedQuery->where($dbColumn, $request->get($requestKey));
                }
            }
            $booked = $bookedQuery->count();
            $leadStatusArrived = AppointmentStatuses::where('is_arrived', 1)->first();

            $arrivedLeadsQuery = clone $bookedQuery;
            $arrivedLeads = $arrivedLeadsQuery
            ->where('appointments.appointment_status_id', 2)
            ->where('appointments.active', 1)
            ->count();

            $consultancyLeadsQuery = clone $bookedQuery;
            $consultancyLeads = $consultancyLeadsQuery
            ->where('appointments.appointment_status_id', 2)
            ->where('appointments.active', 1)
            ->count();

            $notArrivedLeads = $booked - $arrivedLeads;

            $convertedLeadsQuery = clone $bookedQuery;
            $convertedLeads = $convertedLeadsQuery
            ->where('appointments.is_converted', 1)
            ->where('appointments.appointment_status_id', 2)
            ->where('appointments.active', 1)
            ->count();
            $notConvertedLeads = $arrivedLeads - $convertedLeads;
            $convertedRevenue = Invoices::whereIn('appointment_id', Appointments::where('is_converted', 1)
            ->whereIn('lead_id', $leadsGroup->pluck('id'))->pluck('id'))
            ->where('invoice_status_id',3)
            ->count();

            $revenuepaid = Invoices::whereIn('appointment_id', Appointments::where('is_converted', 1)
            ->whereIn('lead_id', $leadsGroup->pluck('id'))->pluck('id'))
            ->where('invoice_status_id',3)
            ->sum('total_price');
            

            $conversionRatio = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
            $conversionRatio_1 = $totalLeads > 0 ? ($booked / $totalLeads) * 100 : 0;
            $conversionRatio_2 = $booked > 0 ? ($arrivedLeads / $booked) * 100 : 0;
            $conversionRatio_3 = $arrivedLeads > 0 ? ($convertedLeads / $arrivedLeads) * 100 : 0;
            $conversionToRevenue = $convertedLeads > 0 ? ($convertedRevenue / $convertedLeads) : 0;
            
            $summaryData[] = array_merge([
                'city' => $cityName ?? '-',
                'center' => '-',
                'center_id' => $leadsGroup->first()->location_id,
                'total_leads' => $totalLeads,
                'booked' => $booked,
                'arrived' => $arrivedLeads,
                'not_arrived' => $notArrivedLeads,
                'consultancy' => $consultancyLeads,
                'converted' => $convertedLeads,
                'not_converted' => $notConvertedLeads,
                'converted_revenue' => $convertedRevenue,
                'conversion_ratio' => $conversionRatio,
                'conversion_ratio_1' => $conversionRatio_1,
                'conversion_ratio_2' => $conversionRatio_2,
                'conversion_ratio_3' => $conversionRatio_3,
                'conversion_to_revenue' => $conversionToRevenue,
                'revenuepaid' => $revenuepaid,
                'revenue_cash_in' => 0,
                'revenue_card_in' => 0,
                'revenue_bank_in' => 0,
                'revenue_wallet_in' => 0,
                'refund' => 0,
                'revenue' =>0,
            ], $sourcesCount);
        }
        if ($request->has('patient_id')) {
            $patientName = Patients::find($request->get('patient_id'))->name ?? 'Unknown Patient';
        } else {
            $patientName = null;
        }
        if ($request->has('doctor_id')) {
            $doctorName = Doctors::find($request->get('doctor_id'))->name ?? 'Unknown Doctor';
        } else {
            $doctorName = null;
        }
        

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.summary_report.summary', compact('summaryData', 'leadSources', 'start_date', 'end_date', 'patientName', 'doctorName'));
                break;
            case 'print':
                return view('admin.reports.summary_report.summaryprint', compact('summaryData', 'leadSources', 'start_date', 'end_date', 'patientName', 'doctorName'));
                break;
            case 'pdf':
                $content = view('admin.reports.summary_report.summarypdf', compact('summaryData', 'leadSources', 'start_date', 'end_date', 'patientName', 'doctorName'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A0', 'landscape');
                return $pdf->stream('Summary Report', 'landscape');
                break;
            case 'excel':
                self::summaryReportExcel($summaryData, $leadSources, $start_date, $end_date);
                break;
            default:
                return view('admin.reports.summary_report.summary', compact('summaryData', 'leadSources', 'start_date', 'end_date', 'patientName'));
                break;
        }
    }

    public function reportleadLoad(Request $request)
    {
        return self::summaryleadReport($request);
    }

    public function summaryleadReport(Request $request)
    {
        // Handle date range
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d 00:00:00', strtotime($date_range[0]));
            $end_date = date('Y-m-d 23:59:59', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        // Retrieve request parameters
        $city_id = $request->get('city_id');
        $patient_id = $request->get('patient_id');
        $doctor_id = $request->get('doctor_id');
        $region_id = $request->get('region_id');
        $service_id = $request->get('service_id');
        $appointment_status_id = $request->get('appointment_status_id');
        $appointment_type_id = $request->get('appointment_type_id');
        $consultancy_type = $request->get('consultancy_type');
        $user_id = $request->get('user_id');
        $re_user_id = $request->get('re_user_id');
        $referred_by = $request->get('referred_by');
        $is_converted = $request->get('is_converted');
        $up_user_id = $request->get('up_user_id');
        $lead_id = $request->get('lead_id');

        // Get location IDs for doctor if provided
        $locationIds = [];
        if (!empty($doctor_id)) {
            $locationIds = DoctorHasLocations::where('user_id', $doctor_id)
                ->pluck('location_id')
                ->toArray();
        }

        // Build leads query
        $leadsQuery = Leads::select('leads.*', 'locations.name as location_name', 'lead_sources.name as source_name', 'lead_statuses.name as status_name')
            ->leftJoin('locations', 'leads.location_id', '=', 'locations.id')
            ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->leftJoin('lead_statuses', 'leads.lead_status_id', '=', 'lead_statuses.id')
            ->where(function ($q) {
                $q->whereIn('leads.city_id', [1, 2, 3, 4, 6, 7, 14, 38, 57, 62])
                    ->orWhereNull('leads.city_id');
            })
            ->where(function ($query) {
                $query->whereNotIn('leads.lead_source_id', [2, 9, 10, 13, 14, 15, 19, 24, 28])
                    ->orWhereNull('leads.lead_source_id');
            })
            ->whereNotIn('leads.lead_status_id', [5, 6, 7, 8])
            ->whereBetween('leads.created_at', [$start_date, $end_date]);

        // Apply filters to leads query
        if ($request->has('lead_id') && !empty($request->get('lead_id'))) {
            $leadsQuery->where('leads.id', $request->get('lead_id'));
        }
        if ($request->has('patient_id') && !empty($request->get('patient_id'))) {
            $leadsQuery->where('leads.patient_id', $request->get('patient_id'));
        }
        if ($request->has('region_id') && !empty($request->get('region_id'))) {
            $leadsQuery->where('leads.region_id', $request->get('region_id'));
        }
        if ($request->has('service_id') && !empty($request->get('service_id'))) {
            $leadsQuery->where('leads.service_id', $request->get('service_id'));
        }
        if ($request->has('user_id') && !empty($request->get('user_id'))) {
            $leadsQuery->where('leads.created_by', $request->get('user_id'));
        }
        if ($request->has('up_user_id') && !empty($request->get('up_user_id'))) {
            $leadsQuery->where('leads.updated_by', $request->get('up_user_id'));
        }
        if ($request->has('re_user_id') && !empty($request->get('re_user_id'))) {
            $leadsQuery->where('leads.converted_by', $request->get('re_user_id'));
        }
        if ($request->has('location_id') && !empty($request->get('location_id'))) {
            $leadsQuery->whereIn('leads.location_id', $request->get('location_id'));
        }
        if ($request->has('lead_source_id') && !empty($request->get('lead_source_id'))) {
            $leadsQuery->whereIn('leads.lead_source_id', $request->get('lead_source_id'));
        }
        if (!empty($city_id)) {
            $leadsQuery->where('locations.city_id', $city_id);
        }
        if (!empty($locationIds)) {
            $leadsQuery->whereIn('leads.location_id', $locationIds);
        }
        if (!empty($doctor_id)) {
            $leadsQuery->leftJoin('appointments', 'appointments.lead_id', '=', 'leads.id');
            $leadsQuery->where('appointments.doctor_id', $doctor_id);
        }

        $leads = $leadsQuery->get();

        // Fetch locations
        $locationsQuery = Locations::query();
        if (!empty($city_id)) {
            $locationsQuery->where('city_id', $city_id);
        }
        $locations = $locationsQuery->get();
        $leadsWithoutLocation = $leadsQuery->whereNull('leads.location_id')->get()->groupBy('city_id');
        $groupedByCenter = $leads->groupBy('location_id');

        // Fetch lead sources
        $leadSource = LeadSources::where('active', 1);
        if ($request->has('lead_source_id') && !empty($request->get('lead_source_id'))) {
            $leadSource->whereIn('id', $request->get('lead_source_id'));
        }
        $leadSources = $leadSource->get();

        // Initialize revenue variables
        $total_revenue_cash_in = 0;
        $total_revenue_card_in = 0;
        $total_revenue_bank_in = 0;
        $total_revenue_wallet_in = 0;
        $total_refund = 0;
        $total_revenue = 0;

        $summaryData = [];

        // Process leads grouped by location
        foreach ($groupedByCenter as $locationId => $leadsGroup) {
            if ($leadsGroup->isEmpty()) {
                continue; // Skip empty lead groups
            }

            $filteredRequest = $request->all();
            $filteredRequest['location_id'] = [$locationId];
            $report_data = Finanaces::generalrevenuereportsummary($filteredRequest, Auth::User()->account_id);
            $totalLeads = $leadsGroup->count();

            $sourcesCount = [];
            foreach ($leadSources as $source) {
                $sourcesCount[$source->name] = $leadsGroup->where('lead_source_id', $source->id)->count();
            }

            $cityid = Locations::where('id', $locationId)->value('city_id');
            $cityName = Cities::where('id', $cityid)->value('name');

            // Build booked appointments query (only those linked to leads in $leadsGroup)
            $leadIds = $leadsGroup->pluck('id')->toArray();
            $bookedQuery = Appointments::query()
                ->whereIn('appointments.lead_id', $leadIds)
                ->where('appointments.location_id', $locationId)
                ->where('appointments.appointment_type_id', 1)
                ->whereNotIn('appointments.appointment_status_id', [4]);

            if ($request->has('date_range_by') && $request->get('date_range_by') == 'scheduled_date') {
                $bookedQuery->whereBetween('appointments.scheduled_date', [$start_date, $end_date]);
            } else {
                $bookedQuery->whereBetween('appointments.created_at', [$start_date, $end_date]);
            }

            // Apply filters to booked query
            $filters = [
                'city_id' => 'appointments.city_id',
                'patient_id' => 'appointments.patient_id',
                'doctor_id' => 'appointments.doctor_id',
                'region_id' => 'appointments.region_id',
                'service_id' => 'appointments.service_id',
                'appointment_status_id' => 'appointments.appointment_status_id',
                'appointment_type_id' => 'appointments.appointment_type_id',
                'consultancy_type' => 'appointments.consultancy_type',
                'user_id' => 'appointments.created_by',
                're_user_id' => 'appointments.converted_by',
                'up_user_id' => 'appointments.updated_by',
                'lead_id' => 'appointments.lead_id',
            ];

            foreach ($filters as $requestKey => $dbColumn) {
                if ($request->has($requestKey) && !empty($request->get($requestKey))) {
                    $bookedQuery->where($dbColumn, $request->get($requestKey));
                }
            }

            $booked = $bookedQuery->count();

            // Build arrived appointments query (only those from booked leads)
            $arrivedLeadsQuery = clone $bookedQuery;
            $arrivedLeads = $arrivedLeadsQuery
                ->where('appointments.appointment_status_id', 2)
                ->where('appointments.active', 1)
                ->count();

            // Consultancy leads (same as arrived for this context)
            $consultancyLeadsQuery = clone $bookedQuery;
            $consultancyLeads = $consultancyLeadsQuery
                ->where('appointments.appointment_status_id', 2)
                ->where('appointments.active', 1)
                ->count();

            $notArrivedLeads = $booked - $arrivedLeads;

            // Build converted appointments query (only those from arrived booked leads)
            $convertedLeadsQuery = clone $bookedQuery;
            $convertedLeads = $convertedLeadsQuery
                ->where('appointments.is_converted', 1)
                ->where('appointments.appointment_status_id', 2)
                ->where('appointments.active', 1)
                ->count();

            $notConvertedLeads = $arrivedLeads - $convertedLeads;

            // Revenue calculations
            $convertedRevenue = Invoices::whereIn('appointment_id', Appointments::query()
                ->whereIn('appointments.lead_id', $leadIds)
                ->where('appointments.is_converted', 1)
                ->when($request->has('lead_id') && !empty($request->get('lead_id')), function ($query) use ($lead_id) {
                    $query->where('appointments.lead_id', $lead_id);
                })
                ->pluck('appointments.id'))
                ->where('invoice_status_id', 3)
                ->count();

            $revenuepaid = Invoices::whereIn('appointment_id', Appointments::query()
                ->whereIn('appointments.lead_id', $leadIds)
                ->where('appointments.is_converted', 1)
                ->when($request->has('lead_id') && !empty($request->get('lead_id')), function ($query) use ($lead_id) {
                    $query->where('appointments.lead_id', $lead_id);
                })
                ->pluck('appointments.id'))
                ->where('invoice_status_id', 3)
                ->sum('total_price');

            $conversionRatio = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
            $conversionRatio_1 = $totalLeads > 0 ? ($booked / $totalLeads) * 100 : 0;
            $conversionRatio_2 = $booked > 0 ? ($arrivedLeads / $booked) * 100 : 0;
            $conversionRatio_3 = $arrivedLeads > 0 ? ($convertedLeads / $arrivedLeads) * 100 : 0;
            $conversionToRevenue = $convertedLeads > 0 ? ($convertedRevenue / $convertedLeads) : 0;

            if ($leadsGroup->first() && $leadsGroup->first()->location_name) {
                $summaryData[] = array_merge([
                    'city' => $cityName ?? '-',
                    'center' => $leadsGroup->first()->location_name,
                    'center_id' => $leadsGroup->first()->location_id,
                    'total_leads' => $totalLeads,
                    'booked' => $booked,
                    'arrived' => $arrivedLeads,
                    'not_arrived' => $notArrivedLeads,
                    'consultancy' => $consultancyLeads,
                    'converted' => $convertedLeads,
                    'not_converted' => $notConvertedLeads,
                    'converted_revenue' => $convertedRevenue,
                    'conversion_ratio' => $conversionRatio,
                    'conversion_ratio_1' => $conversionRatio_1,
                    'conversion_ratio_2' => $conversionRatio_2,
                    'conversion_ratio_3' => $conversionRatio_3,
                    'conversion_to_revenue' => $conversionToRevenue,
                    'revenuepaid' => $revenuepaid,
                    'revenue_cash_in' => $report_data[$locationId]['revenue_cash_in'] ?? 0,
                    'revenue_card_in' => $report_data[$locationId]['revenue_card_in'] ?? 0,
                    'revenue_bank_in' => $report_data[$locationId]['revenue_bank_in'] ?? 0,
                    'revenue_wallet_in' => $report_data[$locationId]['revenue_wallet_in'] ?? 0,
                    'refund' => $report_data[$locationId]['refund_out'] ?? 0,
                    'revenue' => $report_data[$locationId]['in_hand'] ?? 0,
                ], $sourcesCount);
            }

            // Debugging output (uncomment to diagnose)
            \Log::info("Location ID: $locationId, Total Leads: $totalLeads, Booked: $booked, Lead IDs: " . implode(',', $leadIds));
        }

        // Process leads without location
        foreach ($leadsWithoutLocation as $cityId => $leadsGroup) {
            if ($leadsGroup->isEmpty()) {
                continue; // Skip empty lead groups
            }

            $sourcesCount = [];
            foreach ($leadSources as $source) {
                $sourcesCount[$source->name] = $leadsGroup->where('lead_source_id', $source->id)->count();
            }
            $cityName = Cities::where('id', $cityId)->value('name') ?? 'Unknown City';
            $totalLeads = $leadsGroup->count();

            // Build booked appointments query (only those linked to leads in $leadsGroup)
            $leadIds = $leadsGroup->pluck('id')->toArray();
            $bookedQuery = Appointments::query()
                ->whereIn('appointments.lead_id', $leadIds)
                ->where('appointments.appointment_type_id', 1)
                ->whereNotIn('appointments.appointment_status_id', [4]);

            if ($request->has('date_range_by') && $request->get('date_range_by') == 'scheduled_date') {
                $bookedQuery->whereBetween('appointments.scheduled_date', [$start_date, $end_date]);
            } else {
                $bookedQuery->whereBetween('appointments.created_at', [$start_date, $end_date]);
            }

            // Apply filters to booked query
            $filters = [
                'city_id' => 'appointments.city_id',
                'patient_id' => 'appointments.patient_id',
                'doctor_id' => 'appointments.doctor_id',
                'region_id' => 'appointments.region_id',
                'service_id' => 'appointments.service_id',
                'appointment_status_id' => 'appointments.appointment_status_id',
                'appointment_type_id' => 'appointments.appointment_type_id',
                'consultancy_type' => 'appointments.consultancy_type',
                'user_id' => 'appointments.created_by',
                're_user_id' => 'appointments.converted_by',
                'up_user_id' => 'appointments.updated_by',
                'lead_id' => 'appointments.lead_id',
            ];

            foreach ($filters as $requestKey => $dbColumn) {
                if ($request->has($requestKey) && !empty($request->get($requestKey))) {
                    $bookedQuery->where($dbColumn, $request->get($requestKey));
                }
            }

            $booked = $bookedQuery->count();

            // Build arrived appointments query (only those from booked leads)
            $arrivedLeadsQuery = clone $bookedQuery;
            $arrivedLeads = $arrivedLeadsQuery
                ->where('appointments.appointment_status_id', 2)
                ->where('appointments.active', 1)
                ->count();

            // Consultancy leads (same as arrived for this context)
            $consultancyLeadsQuery = clone $bookedQuery;
            $consultancyLeads = $consultancyLeadsQuery
                ->where('appointments.appointment_status_id', 2)
                ->where('appointments.active', 1)
                ->count();

            $notArrivedLeads = $booked - $arrivedLeads;

            // Build converted appointments query (only those from arrived booked leads)
            $convertedLeadsQuery = clone $bookedQuery;
            $convertedLeads = $convertedLeadsQuery
                ->where('appointments.is_converted', 1)
                ->where('appointments.appointment_status_id', 2)
                ->where('appointments.active', 1)
                ->count();

            $notConvertedLeads = $arrivedLeads - $convertedLeads;

            // Revenue calculations
            $convertedRevenue = Invoices::whereIn('appointment_id', Appointments::query()
                ->whereIn('appointments.lead_id', $leadIds)
                ->where('appointments.is_converted', 1)
                ->when($request->has('lead_id') && !empty($request->get('lead_id')), function ($query) use ($lead_id) {
                    $query->where('appointments.lead_id', $lead_id);
                })
                ->pluck('appointments.id'))
                ->where('invoice_status_id', 3)
                ->count();

            $revenuepaid = Invoices::whereIn('appointment_id', Appointments::query()
                ->whereIn('appointments.lead_id', $leadIds)
                ->where('appointments.is_converted', 1)
                ->when($request->has('lead_id') && !empty($request->get('lead_id')), function ($query) use ($lead_id) {
                    $query->where('appointments.lead_id', $lead_id);
                })
                ->pluck('appointments.id'))
                ->where('invoice_status_id', 3)
                ->sum('total_price');

            $conversionRatio = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
            $conversionRatio_1 = $totalLeads > 0 ? ($booked / $totalLeads) * 100 : 0;
            $conversionRatio_2 = $booked > 0 ? ($arrivedLeads / $booked) * 100 : 0;
            $conversionRatio_3 = $arrivedLeads > 0 ? ($convertedLeads / $arrivedLeads) * 100 : 0;
            $conversionToRevenue = $convertedLeads > 0 ? ($convertedRevenue / $convertedLeads) : 0;

            $summaryData[] = array_merge([
                'city' => $cityName ?? '-',
                'center' => '-',
                'center_id' => $leadsGroup->first() ? $leadsGroup->first()->location_id : null,
                'total_leads' => $totalLeads,
                'booked' => $booked,
                'arrived' => $arrivedLeads,
                'not_arrived' => $notArrivedLeads,
                'consultancy' => $consultancyLeads,
                'converted' => $convertedLeads,
                'not_converted' => $notConvertedLeads,
                'converted_revenue' => $convertedRevenue,
                'conversion_ratio' => $conversionRatio,
                'conversion_ratio_1' => $conversionRatio_1,
                'conversion_ratio_2' => $conversionRatio_2,
                'conversion_ratio_3' => $conversionRatio_3,
                'conversion_to_revenue' => $conversionToRevenue,
                'revenuepaid' => $revenuepaid,
                'revenue_cash_in' => 0,
                'revenue_card_in' => 0,
                'revenue_bank_in' => 0,
                'revenue_wallet_in' => 0,
                'refund' => 0,
                'revenue' => 0,
            ], $sourcesCount);

            // Debugging output (uncomment to diagnose)
            \Log::info("City ID: $cityId, Total Leads: $totalLeads, Booked: $booked, Lead IDs: " . implode(',', $leadIds));
        }

        // Fetch patient and doctor names
        if ($request->has('patient_id')) {
            $patientName = Patients::find($request->get('patient_id'))->name ?? 'Unknown Patient';
        } else {
            $patientName = null;
        }
        if ($request->has('doctor_id')) {
            $doctorName = Doctors::find($request->get('doctor_id'))->name ?? 'Unknown Doctor';
        } else {
            $doctorName = null;
        }

        // Handle output based on medium_type
        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.summary_report_lead.summary', compact('summaryData', 'leadSources', 'start_date', 'end_date', 'patientName', 'doctorName'));
                break;
            case 'print':
                return view('admin.reports.summary_report_lead.summaryprint', compact('summaryData', 'leadSources', 'start_date', 'end_date', 'patientName', 'doctorName'));
                break;
            case 'pdf':
                $content = view('admin.reports.summary_report_lead.summarypdf', compact('summaryData', 'leadSources', 'start_date', 'end_date', 'patientName', 'doctorName'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A0', 'landscape');
                return $pdf->stream('Summary Report Lead', 'landscape');
                break;
            case 'excel':
                self::summaryleadReportExcel($summaryData, $leadSources, $start_date, $end_date);
                break;
            default:
                return view('admin.reports.summary_report_lead.summary', compact('summaryData', 'leadSources', 'start_date', 'end_date', 'patientName'));
                break;
        }
    }

    public function reportconversionLoad(Request $request)
    {
        return self::summaryconversionReport($request);
    }

    public function summaryconversionReport(Request $request)
    {
        // Handle date range
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d 00:00:00', strtotime($date_range[0]));
            $end_date = date('Y-m-d 23:59:59', strtotime($date_range[1]));
        } else {
            $start_date = date('Y-m-d 00:00:00');
            $end_date = date('Y-m-d 23:59:59');
        }

        // Retrieve request parameters
        $city_id = $request->get('city_id');
        $patient_id = $request->get('patient_id');
        $doctor_id = $request->get('doctor_id');
        $region_id = $request->get('region_id');
        $service_id = $request->get('service_id');
        $appointment_status_id = $request->get('appointment_status_id');
        $appointment_type_id = $request->get('appointment_type_id');
        $consultancy_type = $request->get('consultancy_type');
        $user_id = $request->get('user_id');
        $re_user_id = $request->get('re_user_id');
        $referred_by = $request->get('referred_by');
        $is_converted = $request->get('is_converted');
        $up_user_id = $request->get('up_user_id');
        $lead_id = $request->get('lead_id');

        // Get location IDs for doctor if provided
        $locationIds = [];
        if (!empty($doctor_id)) {
            $locationIds = DoctorHasLocations::where('user_id', $doctor_id)
                ->pluck('location_id')
                ->toArray();
        }

        // Build leads query
        $leadsQuery = Leads::select('leads.*', 'locations.name as location_name', 'regions.name as region_name', 'lead_sources.name as source_name')
            ->leftJoin('locations', 'leads.location_id', '=', 'locations.id')
            ->leftJoin('regions', 'locations.region_id', '=', 'regions.id')
            ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->where(function ($q) {
                $q->whereIn('leads.city_id', [1, 2, 3, 4, 6, 7, 14, 38, 57, 62])
                    ->orWhereNull('leads.city_id');
            })
            ->where(function ($query) {
                $query->whereNotIn('leads.lead_source_id', [2, 9, 10, 13, 14, 15, 19, 24, 28])
                    ->orWhereNull('leads.lead_source_id');
            })
            ->whereNotIn('leads.lead_status_id', [5, 6, 7, 8])
            ->whereBetween('leads.created_at', [$start_date, $end_date]);

        if ($request->has('patient_id') && !empty($request->get('patient_id'))) {
            $leadsQuery->where('leads.patient_id', $request->get('patient_id'));
        }
        if ($request->has('region_id') && !empty($request->get('region_id'))) {
            $leadsQuery->where('locations.region_id', $request->get('region_id'));
        }
        if ($request->has('service_id') && !empty($request->get('service_id'))) {
            $leadsQuery->where('leads.service_id', $request->get('service_id'));
        }
        if ($request->has('user_id') && !empty($request->get('user_id'))) {
            $leadsQuery->where('leads.created_by', $request->get('user_id'));
        }
        if ($request->has('up_user_id') && !empty($request->get('up_user_id'))) {
            $leadsQuery->where('leads.updated_by', $request->get('up_user_id'));
        }
        if ($request->has('re_user_id') && !empty($request->get('re_user_id'))) {
            $leadsQuery->where('leads.converted_by', $request->get('re_user_id'));
        }
        if ($request->has('location_id') && !empty($request->get('location_id'))) {
            $leadsQuery->whereIn('leads.location_id', $request->get('location_id'));
        }
        if ($request->has('lead_source_id') && !empty($request->get('lead_source_id'))) {
            $leadsQuery->whereIn('leads.lead_source_id', $request->get('lead_source_id'));
        }
        if (!empty($city_id)) {
            $leadsQuery->where('locations.city_id', $city_id);
        }
        if (!empty($locationIds)) {
            $leadsQuery->whereIn('leads.location_id', $locationIds);
        }
        if (!empty($doctor_id)) {
            $leadsQuery->leftJoin('appointments', 'appointments.lead_id', '=', 'leads.id');
            $leadsQuery->where('appointments.doctor_id', $doctor_id);
        }

        $leads = $leadsQuery->get();

        // Group by region and then by location
        $groupedByRegion = $leads->groupBy('region_id')->map(function ($regionLeads) {
            return $regionLeads->groupBy('location_id');
        });

        $leadSources = LeadSources::where('active', 1)->get();
        $summaryData = [];

        // Grand totals
        $grandTotals = [
            'booking_quota' => 0, 'booked_body_contouring' => 0, 'booked_hifu_facelift' => 0, 'booked_trilogy_ice' => 0,
            'booked_facials' => 0, 'booked_others' => 0, 'booked' => 0, 'arrived_body_contouring' => 0,
            'arrived_hifu_facelift' => 0, 'arrived_trilogy_ice' => 0, 'arrived_facials' => 0, 'arrived_others' => 0,
            'arrived' => 0, 'walk_ins' => 0, 'arrived_call_center' => 0, 'converted' => 0
        ];

        foreach ($groupedByRegion as $regionId => $centers) {
            $region = Regions::find($regionId);
            $regionName = $region ? $region->name : 'Unknown Region';
            $regionData = [
                'region' => $regionName,
                'centers' => [],
                'subtotals' => [
                    'booking_quota' => 0, 'booked_body_contouring' => 0, 'booked_hifu_facelift' => 0, 'booked_trilogy_ice' => 0,
                    'booked_facials' => 0, 'booked_others' => 0, 'booked' => 0, 'arrived_body_contouring' => 0,
                    'arrived_hifu_facelift' => 0, 'arrived_trilogy_ice' => 0, 'arrived_facials' => 0, 'arrived_others' => 0,
                    'arrived' => 0, 'walk_ins' => 0, 'arrived_call_center' => 0, 'converted' => 0
                ]
            ];

            foreach ($centers as $locationId => $leadsGroup) {
                $location = Locations::find($locationId);
                $centerName = $location ? $location->name : '-';

                $bookingQuery = Appointments::query()
                    ->where('appointments.location_id', $locationId);

                $bookedQuery = Appointments::query()
                    ->where('appointments.location_id', $locationId)
                    ->where('appointment_type_id', 1)
                    ->whereNotIn('appointments.appointment_status_id', [4]);

                if ($request->has('date_range_by') && $request->get('date_range_by') == 'scheduled_date') {
                    $bookedQuery->whereBetween('appointments.scheduled_date', [$start_date, $end_date]);
                } else {
                    $bookedQuery->whereBetween('appointments.created_at', [$start_date, $end_date]);
                }

                if ($request->has('date_range_by') && $request->get('date_range_by') == 'scheduled_date') {
                    $bookingQuery->whereBetween('appointments.scheduled_date', [$start_date, $end_date]);
                } else {
                    $bookingQuery->whereBetween('appointments.created_at', [$start_date, $end_date]);
                }

                $filters = [
                    'city_id' => 'appointments.city_id', 'patient_id' => 'appointments.patient_id',
                    'doctor_id' => 'appointments.doctor_id', 'region_id' => 'appointments.region_id',
                    'service_id' => 'appointments.service_id', 'appointment_status_id' => 'appointments.appointment_status_id',
                    'appointment_type_id' => 'appointments.appointment_type_id', 'consultancy_type' => 'appointments.consultancy_type',
                    'user_id' => 'appointments.created_by', 're_user_id' => 'appointments.converted_by',
                    'up_user_id' => 'appointments.updated_by'
                ];

                foreach ($filters as $requestKey => $dbColumn) {
                    if ($request->has($requestKey) && !empty($request->get($requestKey))) {
                        $bookedQuery->where($dbColumn, $request->get($requestKey));
                    }
                }
                foreach ($filters as $requestKey => $dbColumn) {
                    if ($request->has($requestKey) && !empty($request->get($requestKey))) {
                        $bookingQuery->where($dbColumn, $request->get($requestKey));
                    }
                }

                $bookQuota = $leadsGroup->count();
                $bookingQuota = $leadsGroup->count();
                $bookedBodyContouring = (clone $bookedQuery)->where('appointments.service_id', 3)->where('appointments.active', 1)->count();
                $bookedHifuFacelift = (clone $bookedQuery)->where('appointments.service_id', 2)->where('appointments.active', 1)->count();
                $bookedTrilogyIce = (clone $bookedQuery)->where('appointments.service_id', 4)->where('appointments.active', 1)->count();
                $bookedFacials = (clone $bookedQuery)->where('appointments.service_id', 116)->where('appointments.active', 1)->count();
                $bookedOthers = $bookedQuery->count() - ($bookedBodyContouring + $bookedHifuFacelift + $bookedTrilogyIce + $bookedFacials);
                $booked = $bookedQuery->count();

                $arrivedQuery = (clone $bookedQuery)->where('appointments.appointment_status_id', 2);
                $arrivedBodyContouring = (clone $arrivedQuery)->where('appointments.service_id', 3)->where('appointments.active', 1)->count();
                $arrivedHifuFacelift = (clone $arrivedQuery)->where('appointments.service_id', 2)->where('appointments.active', 1)->count();
                $arrivedTrilogyIce = (clone $arrivedQuery)->where('appointments.service_id', 4)->where('appointments.active', 1)->count();
                $arrivedFacials = (clone $arrivedQuery)->where('appointments.service_id', 116)->where('appointments.active', 1)->count();
                $arrivedOthers = $arrivedQuery->count() - ($arrivedBodyContouring + $arrivedHifuFacelift + $arrivedTrilogyIce + $arrivedFacials);
                $arrivedLeads = $arrivedQuery->count();
                $walkInsQuery = Appointments::query()
                    ->where('appointments.location_id', $locationId)
                    ->where('appointment_type_id', 1)
                    ->where('appointments.appointment_status_id', 2)
                    ->where('appointments.active', 1)
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('leads')
                            ->whereColumn('leads.id', 'appointments.lead_id')
                            ->where('leads.lead_source_id', 16);
                    })
                    ->whereBetween('appointments.created_at', [$start_date, $end_date]);

                foreach ($filters as $requestKey => $dbColumn) {
                    if ($request->has($requestKey) && !empty($request->get($requestKey))) {
                        $walkInsQuery->where($dbColumn, $request->get($requestKey));
                    }
                }

                $walkIns = $walkInsQuery->count();
                $arrivedCallCenter = $arrivedLeads - $walkIns;
                $convertedLeads = (clone $arrivedQuery)->where('appointments.is_converted', 1)->where('appointments.active', 1)->count();

                $centerData = [
                    'center' => $centerName,
                    'booking_quota' => $bookQuota,
                    'booked_body_contouring' => $bookedBodyContouring,
                    'booked_hifu_facelift' => $bookedHifuFacelift,
                    'booked_trilogy_ice' => $bookedTrilogyIce,
                    'booked_facials' => $bookedFacials,
                    'booked_others' => $bookedOthers,
                    'booked' => $booked,
                    'arrived_body_contouring' => $arrivedBodyContouring,
                    'arrived_hifu_facelift' => $arrivedHifuFacelift,
                    'arrived_trilogy_ice' => $arrivedTrilogyIce,
                    'arrived_facials' => $arrivedFacials,
                    'arrived_others' => $arrivedOthers,
                    'arrived' => $arrivedLeads,
                    'walk_ins' => $walkIns,
                    'arrived_call_center' => $arrivedCallCenter,
                    'converted' => $convertedLeads,
                    'booking_ratio' => $bookingQuota > 0 ? number_format(($booked / $bookingQuota) * 100, 2) : 0,
                    'walkins_in_arrivals' => $arrivedLeads > 0 ? number_format(($walkIns / $arrivedLeads) * 100, 2) : 0,
                    'ratio_walkins_to_booked' => $arrivedCallCenter > 0 ? number_format(($walkIns / $arrivedCallCenter) * 100, 2) : 0,
                    'arrival_booked' => $booked > 0 ? number_format(($arrivedLeads / $booked) * 100, 2) : 0,
                    'arrival_total' => $bookingQuota > 0 ? number_format(($arrivedLeads / $bookingQuota) * 100, 2) : 0,
                    'conversion_ratio' => $arrivedLeads > 0 ? number_format(($convertedLeads / $arrivedLeads) * 100, 2) : 0
                ];

                // Update region subtotals
                foreach ($centerData as $key => $value) {
                    if (in_array($key, array_keys($regionData['subtotals']))) {
                        $regionData['subtotals'][$key] += $value;
                    }
                }

                $regionData['centers'][] = $centerData;
            }

            // Calculate region subtotals percentages
            $regionData['subtotals']['booking_ratio'] = $regionData['subtotals']['booking_quota'] > 0 ? number_format(($regionData['subtotals']['booked'] / $regionData['subtotals']['booking_quota']) * 100, 2) : 0;
            $regionData['subtotals']['walkins_in_arrivals'] = $regionData['subtotals']['arrived'] > 0 ? number_format(($regionData['subtotals']['walk_ins'] / $regionData['subtotals']['arrived']) * 100, 2) : 0;
            $regionData['subtotals']['ratio_walkins_to_booked'] = $regionData['subtotals']['arrived_call_center'] > 0 ? number_format(($regionData['subtotals']['walk_ins'] / $regionData['subtotals']['arrived_call_center']) * 100, 2) : 0;
            $regionData['subtotals']['arrival_booked'] = $regionData['subtotals']['booked'] > 0 ? number_format(($regionData['subtotals']['arrived'] / $regionData['subtotals']['booked']) * 100, 2) : 0;
            $regionData['subtotals']['arrival_total'] = $regionData['subtotals']['booking_quota'] > 0 ? number_format(($regionData['subtotals']['arrived'] / $regionData['subtotals']['booking_quota']) * 100, 2) : 0;
            $regionData['subtotals']['conversion_ratio'] = $regionData['subtotals']['arrived'] > 0 ? number_format(($regionData['subtotals']['converted'] / $regionData['subtotals']['arrived']) * 100, 2) : 0;

            $summaryData[] = $regionData;

            // Update grand totals
            foreach ($regionData['subtotals'] as $key => $value) {
                if (in_array($key, array_keys($grandTotals))) {
                    $grandTotals[$key] += $value;
                }
            }
        }

        // Calculate grand totals percentages
        $grandTotals['booking_ratio'] = $grandTotals['booking_quota'] > 0 ? number_format(($grandTotals['booked'] / $grandTotals['booking_quota']) * 100, 2) : 0;
        $grandTotals['walkins_in_arrivals'] = $grandTotals['arrived'] > 0 ? number_format(($grandTotals['walk_ins'] / $grandTotals['arrived']) * 100, 2) : 0;
        $grandTotals['ratio_walkins_to_booked'] = $grandTotals['arrived_call_center'] > 0 ? number_format(($grandTotals['walk_ins'] / $grandTotals['arrived_call_center']) * 100, 2) : 0;
        $grandTotals['arrival_booked'] = $grandTotals['booked'] > 0 ? number_format(($grandTotals['arrived'] / $grandTotals['booked']) * 100, 2) : 0;
        $grandTotals['arrival_total'] = $grandTotals['booking_quota'] > 0 ? number_format(($grandTotals['arrived'] / $grandTotals['booking_quota']) * 100, 2) : 0;
        $grandTotals['conversion_ratio'] = $grandTotals['arrived'] > 0 ? number_format(($grandTotals['converted'] / $grandTotals['arrived']) * 100, 2) : 0;

        // Handle output
        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.summary_report_conversion.summary', compact('summaryData', 'start_date', 'end_date', 'grandTotals'));
            case 'print':
                return view('admin.reports.summary_report_conversion.summaryprint', compact('summaryData', 'start_date', 'end_date', 'grandTotals'));
            case 'pdf':
                $content = view('admin.reports.summary_report_conversion.summarypdf', compact('summaryData', 'start_date', 'end_date', 'grandTotals'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A0', 'landscape');
                return $pdf->stream('Summary Report Lead', 'landscape');
            case 'excel':
                self::summaryconversionReportExcel($summaryData, $start_date, $end_date);
                break;
            default:
                return view('admin.reports.summary_report_conversion.summary', compact('summaryData', 'start_date', 'end_date', 'grandTotals'));
        }
    }
    /**
     * Load Clients by Appointment Status (Date Wise) Report
     */
    private static function summaryReportExcel($summaryData, $leadSources, $start_date, $end_date)
    {
        $spreadsheet = new Spreadsheet();
        $Excel_writer = new Xlsx($spreadsheet);
        $Excel_writer->setPreCalculateFormulas(false);

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        // Setting report header information
        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);
        $activeSheet->setCellValue('A2', 'Generated on')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        // Defining headers for summary data columns
        $headers = [
            'A4' => 'City',
            'B4' => 'Location', 
            'C4' => 'Total Leads', 
            'D4' => 'Booked', 
            'E4' => 'Converted Revenue (B/T)', 
            'F4' => 'Arrived', 
            'G4' => 'Not Arrived', 
            'H4' => 'Converted Revenue (A/B)', 
            'I4' => 'Converted', 
            'J4' => 'Not Converted', 
            'K4' => 'Converted Revenue (C/A)', 
            'L4' => 'Converted Revenue (C/T)', 
            'M4' => 'Conversion Ratio (%)', 
            'N4' => 'Conversion to Revenue',
            'O4' => 'Total Revenue Cash In',
            'P4' => 'Total Revenue Card In',
            'Q4' => 'Total Revenue Bank In',
            'R4' => 'Total Revenue Wallet In',
            'S4' => 'Total Refund',
            'T4' => 'Total Hand-In'
        ];
        $column = 'U';

        // Adding lead sources as columns dynamically
        foreach ($leadSources as $source) {
            $headers[$column . '4'] = $source->name;
            $column++;
        }

        foreach ($headers as $cell => $header) {
            $activeSheet->setCellValue($cell, $header)->getStyle($cell)->getFont()->setBold(true);
        }

        // Initializing total variables for each relevant column
        $totalLeadsSum = $bookedSum = $arrivedSum = $notArrivedSum = $convertedSum = $notConvertedSum = 0;
        $convertedRevenueSum = $revenuecashinSum = $revenuecardinSum = $revenuebankinSum = $revenuewalletinSum = $revenuerefundSum = $revenuerevenueSum = 0;
        $sourcesSum = array_fill_keys(array_map(function($s) { return $s['name']; }, $leadSources->toArray()), 0);

        // Filling in the data rows
        $row = 5;
        foreach ($summaryData as $data) {
            $activeSheet->setCellValue('A' . $row, $data['city']);
            $activeSheet->setCellValue('B' . $row, $data['center']);
            $activeSheet->setCellValue('C' . $row, $data['total_leads']);
            $activeSheet->setCellValue('D' . $row, $data['booked']);
            $activeSheet->setCellValue('E' . $row, $data['conversion_ratio_1']);
            $activeSheet->setCellValue('F' . $row, $data['arrived']);
            $activeSheet->setCellValue('G' . $row, $data['not_arrived']);
            $activeSheet->setCellValue('H' . $row, $data['conversion_ratio_2']);
            $activeSheet->setCellValue('I' . $row, $data['converted']);
            $activeSheet->setCellValue('J' . $row, $data['not_converted']);
            $activeSheet->setCellValue('K' . $row, $data['conversion_ratio_3']);
            $activeSheet->setCellValue('L' . $row, $data['conversion_ratio']);
            $activeSheet->setCellValue('M' . $row, $data['converted_revenue']);
            $activeSheet->setCellValue('N' . $row, $data['conversion_to_revenue']);
            $activeSheet->setCellValue('O' . $row, $data['revenue_cash_in']);
            $activeSheet->setCellValue('P' . $row, $data['revenue_card_in']);
            $activeSheet->setCellValue('Q' . $row, $data['revenue_bank_in']);
            $activeSheet->setCellValue('R' . $row, $data['revenue_wallet_in']);
            $activeSheet->setCellValue('S' . $row, $data['refund']);
            $activeSheet->setCellValue('T' . $row, $data['revenue']);


            // Accumulate totals
            $totalLeadsSum += $data['total_leads'];
            $bookedSum += $data['booked'];
            $arrivedSum += $data['arrived'];
            $notArrivedSum += $data['not_arrived'];
            $convertedSum += $data['converted'];
            $notConvertedSum += $data['not_converted'];
            $convertedRevenueSum += $data['converted_revenue'];
            $revenuecashinSum += $data['revenue_cash_in'];
            $revenuecardinSum += $data['revenue_card_in'];
            $revenuebankinSum += $data['revenue_bank_in'];
            $revenuewalletinSum += $data['revenue_wallet_in'];
            $revenuerefundSum += $data['refund'];
            $revenuerevenueSum += $data['revenue'];

            // Adding lead source counts and summing totals for each source
            $col = 'U';
            foreach ($leadSources as $source) {
                $sourceValue = $data[$source->name] ?? 0;
                $activeSheet->setCellValue($col . $row, $sourceValue);
                $sourcesSum[$source->name] += $sourceValue;
                $col++;
            }
            $row++;
        }

        // Adding the totals row at the end
        $activeSheet->setCellValue('A' . $row, 'Totals');
        $activeSheet->setCellValue('B' . $row, '-');
        $activeSheet->setCellValue('C' . $row, $totalLeadsSum);
        $activeSheet->setCellValue('D' . $row, $bookedSum);
        $activeSheet->setCellValue('E' . $row, '-');
        $activeSheet->setCellValue('F' . $row, $arrivedSum);
        $activeSheet->setCellValue('G' . $row, $notArrivedSum);
        $activeSheet->setCellValue('H' . $row, '-');
        $activeSheet->setCellValue('I' . $row, $convertedSum);
        $activeSheet->setCellValue('J' . $row, $notConvertedSum);
        $activeSheet->setCellValue('K' . $row, '-');
        $activeSheet->setCellValue('L' . $row, '-');
        $activeSheet->setCellValue('M' . $row, $convertedRevenueSum);
        $activeSheet->setCellValue('N' . $row, '-'); // Skip total for calculated values
        $activeSheet->setCellValue('O' . $row, $revenuecashinSum);
        $activeSheet->setCellValue('P' . $row, $revenuecardinSum);
        $activeSheet->setCellValue('Q' . $row, $revenuebankinSum);
        $activeSheet->setCellValue('R' . $row, $revenuewalletinSum);
        $activeSheet->setCellValue('S' . $row, $revenuerefundSum);
        $activeSheet->setCellValue('T' . $row, $revenuerevenueSum);


        $col = 'U';
        foreach ($leadSources as $source) {
            $activeSheet->setCellValue($col . $row, $sourcesSum[$source->name]);
            $col++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="LeadSummary_' . $start_date . '_to_' . $end_date . '.xlsx"');
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    private static function summaryleadReportExcel($summaryData, $leadSources, $start_date, $end_date)
    {
        $spreadsheet = new Spreadsheet();
        $Excel_writer = new Xlsx($spreadsheet);
        $Excel_writer->setPreCalculateFormulas(false);

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        // Setting report header information
        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);
        $activeSheet->setCellValue('A2', 'Generated on')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        // Defining headers for summary data columns
        $headers = [
            'A4' => 'City',
            'B4' => 'Location', 
            'C4' => 'Total Leads', 
            'D4' => 'Booked', 
            'E4' => 'Converted Revenue (B/T)', 
            'F4' => 'Arrived', 
            'G4' => 'Not Arrived', 
            'H4' => 'Converted Revenue (A/B)', 
            'I4' => 'Converted', 
            'J4' => 'Not Converted', 
            'K4' => 'Converted Revenue (C/A)', 
            'L4' => 'Converted Revenue (C/T)', 
            'M4' => 'Conversion Ratio (%)', 
        ];
        $column = 'N';

        // Adding lead sources as columns dynamically
        foreach ($leadSources as $source) {
            $headers[$column . '4'] = $source->name;
            $column++;
        }

        foreach ($headers as $cell => $header) {
            $activeSheet->setCellValue($cell, $header)->getStyle($cell)->getFont()->setBold(true);
        }

        // Initializing total variables for each relevant column
        $totalLeadsSum = $bookedSum = $arrivedSum = $notArrivedSum = $convertedSum = $notConvertedSum = 0;
        $convertedRevenueSum = $revenuecashinSum = $revenuecardinSum = $revenuebankinSum = $revenuewalletinSum = $revenuerefundSum = $revenuerevenueSum = 0;
        $sourcesSum = array_fill_keys(array_map(function($s) { return $s['name']; }, $leadSources->toArray()), 0);

        // Filling in the data rows
        $row = 5;
        foreach ($summaryData as $data) {
            $activeSheet->setCellValue('A' . $row, $data['city']);
            $activeSheet->setCellValue('B' . $row, $data['center']);
            $activeSheet->setCellValue('C' . $row, $data['total_leads']);
            $activeSheet->setCellValue('D' . $row, $data['booked']);
            $activeSheet->setCellValue('E' . $row, $data['conversion_ratio_1']);
            $activeSheet->setCellValue('F' . $row, $data['arrived']);
            $activeSheet->setCellValue('G' . $row, $data['not_arrived']);
            $activeSheet->setCellValue('H' . $row, $data['conversion_ratio_2']);
            $activeSheet->setCellValue('I' . $row, $data['converted']);
            $activeSheet->setCellValue('J' . $row, $data['not_converted']);
            $activeSheet->setCellValue('K' . $row, $data['conversion_ratio_3']);
            $activeSheet->setCellValue('L' . $row, $data['conversion_ratio']);
            $activeSheet->setCellValue('M' . $row, $data['converted_revenue']);


            // Accumulate totals
            $totalLeadsSum += $data['total_leads'];
            $bookedSum += $data['booked'];
            $arrivedSum += $data['arrived'];
            $notArrivedSum += $data['not_arrived'];
            $convertedSum += $data['converted'];
            $notConvertedSum += $data['not_converted'];
            $convertedRevenueSum += $data['converted_revenue'];
            $revenuecashinSum += $data['revenue_cash_in'];
            $revenuecardinSum += $data['revenue_card_in'];
            $revenuebankinSum += $data['revenue_bank_in'];
            $revenuewalletinSum += $data['revenue_wallet_in'];
            $revenuerefundSum += $data['refund'];
            $revenuerevenueSum += $data['revenue'];

            // Adding lead source counts and summing totals for each source
            $col = 'N';
            foreach ($leadSources as $source) {
                $sourceValue = $data[$source->name] ?? 0;
                $activeSheet->setCellValue($col . $row, $sourceValue);
                $sourcesSum[$source->name] += $sourceValue;
                $col++;
            }
            $row++;
        }

        // Adding the totals row at the end
        $activeSheet->setCellValue('A' . $row, 'Totals');
        $activeSheet->setCellValue('B' . $row, '-');
        $activeSheet->setCellValue('C' . $row, $totalLeadsSum);
        $activeSheet->setCellValue('D' . $row, $bookedSum);
        $activeSheet->setCellValue('E' . $row, '-');
        $activeSheet->setCellValue('F' . $row, $arrivedSum);
        $activeSheet->setCellValue('G' . $row, $notArrivedSum);
        $activeSheet->setCellValue('H' . $row, '-');
        $activeSheet->setCellValue('I' . $row, $convertedSum);
        $activeSheet->setCellValue('J' . $row, $notConvertedSum);
        $activeSheet->setCellValue('K' . $row, '-');
        $activeSheet->setCellValue('L' . $row, '-');
        $activeSheet->setCellValue('M' . $row, $convertedRevenueSum);


        $col = 'N';
        foreach ($leadSources as $source) {
            $activeSheet->setCellValue($col . $row, $sourcesSum[$source->name]);
            $col++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="LeadSummary_' . $start_date . '_to_' . $end_date . '.xlsx"');
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    private static function summaryconversionReportExcel($summaryData, $start_date, $end_date)
    {
        $spreadsheet = new Spreadsheet();
        $Excel_writer = new Xlsx($spreadsheet);
        $Excel_writer->setPreCalculateFormulas(false);

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Summary');

        // Define headers for individual cells
        $headers = [
            'A4' => 'Region / Center',
            'B4' => 'Booking Quota',
            'C4' => 'Body Contouring',
            'D4' => 'HIFU Facelift',
            'E4' => 'Trilogy Ice',
            'F4' => 'Facials',
            'G4' => 'Others',
            'H4' => 'Booked',
            'I4' => 'Booked Ratio (%)',
            'J4' => 'Body Contouring Arrived',
            'K4' => 'Body Contouring %',
            'L4' => 'HIFU Facelift Arrived',
            'M4' => 'Trilogy Ice Arrived',
            'N4' => 'Facials Arrived',
            'O4' => 'Others Arrived',
            'P4' => 'Arrived',
            'Q4' => 'Walk-Ins',
            'R4' => 'Arrived Call Center',
            'S4' => 'Walkins % in Arrivals',
            'T4' => 'Ratio of Walkins to Booked Arrivals',
            'U4' => 'Arrival %age Booked',
            'V4' => 'Arrival %age Total',
        ];
        // Define headers for merged cells
        $mergedHeaders = [
            'B3:I3' => 'BOOKINGS',
            'J3:V3' => 'ARRIVALS',
            'W3:X3' => 'CONVERSIONS',
        ];
        $column = 'W';

        // Add Converted header only if is_converted is 'all' or 'converted'
        if (request()->get('is_converted') === 'all' || request()->get('is_converted') === 'converted') {
            $headers[$column . '4'] = 'Converted';
            $column++;
        }
        $headers[$column . '4'] = 'Converted Ratio (%)';

        // Set report header information
        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);
        $activeSheet->setCellValue('A2', 'Generated on')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d H:i A'));

        // Apply merged headers
        foreach ($mergedHeaders as $range => $header) {
            $activeSheet->mergeCells($range);
            $startCell = explode(':', $range)[0]; // Get the first cell of the range (e.g., B3)
            $activeSheet->setCellValue($startCell, $header)->getStyle($startCell)->getFont()->setBold(true);
            $activeSheet->getStyle($startCell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Apply single-cell headers
        foreach ($headers as $cell => $header) {
            $activeSheet->setCellValue($cell, $header)->getStyle($cell)->getFont()->setBold(true);
            $activeSheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Apply header styling
        $headerStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF8F8F8'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFDDDDDD'],
                ],
            ],
        ];
        $activeSheet->getStyle('A3:' . $column . '4')->applyFromArray($headerStyle);

        // Initialize grand totals
        $grandTotals = [
            'booking_quota' => 0, 'booked_body_contouring' => 0, 'booked_hifu_facelift' => 0, 'booked_trilogy_ice' => 0,
            'booked_facials' => 0, 'booked_others' => 0, 'booked' => 0, 'arrived_body_contouring' => 0,
            'arrived_hifu_facelift' => 0, 'arrived_trilogy_ice' => 0, 'arrived_facials' => 0, 'arrived_others' => 0,
            'arrived' => 0, 'walk_ins' => 0, 'arrived_call_center' => 0, 'converted' => 0
        ];

        $row = 6;
        foreach ($summaryData as $regionData) {
            // Region header
            $activeSheet->setCellValue('A' . $row, $regionData['region']);
            $activeSheet->getStyle('A' . $row . ':' . $column . $row)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF2F2F2'],
                ],
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
            ]);
            $activeSheet->mergeCells('A' . $row . ':' . $column . $row);
            $row++;

            // Center data
            foreach ($regionData['centers'] as $data) {
                $bodyContouringPercentage = $data['booked_body_contouring'] > 0 ? 
                    number_format(($data['arrived_body_contouring'] / $data['booked_body_contouring']) * 100, 2) : 'N/A';

                $activeSheet->setCellValue('A' . $row, $data['center']);
                $activeSheet->setCellValue('B' . $row, $data['booking_quota']);
                $activeSheet->setCellValue('C' . $row, $data['booked_body_contouring']);
                $activeSheet->setCellValue('D' . $row, $data['booked_hifu_facelift']);
                $activeSheet->setCellValue('E' . $row, $data['booked_trilogy_ice']);
                $activeSheet->setCellValue('F' . $row, $data['booked_facials']);
                $activeSheet->setCellValue('G' . $row, $data['booked_others']);
                $activeSheet->setCellValue('H' . $row, $data['booked']);
                $activeSheet->setCellValue('I' . $row, $data['booking_ratio']);
                $activeSheet->setCellValue('J' . $row, $data['arrived_body_contouring']);
                $activeSheet->setCellValue('K' . $row, $bodyContouringPercentage);
                $activeSheet->setCellValue('L' . $row, $data['arrived_hifu_facelift']);
                $activeSheet->setCellValue('M' . $row, $data['arrived_trilogy_ice']);
                $activeSheet->setCellValue('N' . $row, $data['arrived_facials']);
                $activeSheet->setCellValue('O' . $row, $data['arrived_others']);
                $activeSheet->setCellValue('P' . $row, $data['arrived']);
                $activeSheet->setCellValue('Q' . $row, $data['walk_ins']);
                $activeSheet->setCellValue('R' . $row, $data['arrived_call_center']);
                $activeSheet->setCellValue('S' . $row, $data['walkins_in_arrivals']);
                $activeSheet->setCellValue('T' . $row, $data['ratio_walkins_to_booked']);
                $activeSheet->setCellValue('U' . $row, $data['arrival_booked']);
                $activeSheet->setCellValue('V' . $row, $data['arrival_total']);
                
                $col = 'W';
                if (request()->get('is_converted') === 'all' || request()->get('is_converted') === 'converted') {
                    $activeSheet->setCellValue($col . $row, $data['converted']);
                    $col++;
                }
                $activeSheet->setCellValue($col . $row, $data['conversion_ratio']);

                // Accumulate grand totals
                foreach ($grandTotals as $key => &$value) {
                    $value += $data[$key] ?? 0;
                }
                $row++;
            }

            // Region subtotal
            $activeSheet->setCellValue('A' . $row, 'Subtotal ' . $regionData['region']);
            $activeSheet->setCellValue('B' . $row, $regionData['subtotals']['booking_quota']);
            $activeSheet->setCellValue('C' . $row, $regionData['subtotals']['booked_body_contouring']);
            $activeSheet->setCellValue('D' . $row, $regionData['subtotals']['booked_hifu_facelift']);
            $activeSheet->setCellValue('E' . $row, $regionData['subtotals']['booked_trilogy_ice']);
            $activeSheet->setCellValue('F' . $row, $regionData['subtotals']['booked_facials']);
            $activeSheet->setCellValue('G' . $row, $regionData['subtotals']['booked_others']);
            $activeSheet->setCellValue('H' . $row, $regionData['subtotals']['booked']);
            $activeSheet->setCellValue('I' . $row, $regionData['subtotals']['booking_ratio']);
            $activeSheet->setCellValue('J' . $row, $regionData['subtotals']['arrived_body_contouring']);
            $activeSheet->setCellValue('K' . $row, $regionData['subtotals']['booked_body_contouring'] > 0 ? 
                number_format(($regionData['subtotals']['arrived_body_contouring'] / $regionData['subtotals']['booked_body_contouring']) * 100, 2) : 'N/A');
            $activeSheet->setCellValue('L' . $row, $regionData['subtotals']['arrived_hifu_facelift']);
            $activeSheet->setCellValue('M' . $row, $regionData['subtotals']['arrived_trilogy_ice']);
            $activeSheet->setCellValue('N' . $row, $regionData['subtotals']['arrived_facials']);
            $activeSheet->setCellValue('O' . $row, $regionData['subtotals']['arrived_others']);
            $activeSheet->setCellValue('P' . $row, $regionData['subtotals']['arrived']);
            $activeSheet->setCellValue('Q' . $row, $regionData['subtotals']['walk_ins']);
            $activeSheet->setCellValue('R' . $row, $regionData['subtotals']['arrived_call_center']);
            $activeSheet->setCellValue('S' . $row, $regionData['subtotals']['walkins_in_arrivals']);
            $activeSheet->setCellValue('T' . $row, $regionData['subtotals']['ratio_walkins_to_booked']);
            $activeSheet->setCellValue('U' . $row, $regionData['subtotals']['arrival_booked']);
            $activeSheet->setCellValue('V' . $row, $regionData['subtotals']['arrival_total']);
            
            $col = 'W';
            if (request()->get('is_converted') === 'all' || request()->get('is_converted') === 'converted') {
                $activeSheet->setCellValue($col . $row, $regionData['subtotals']['converted']);
                $col++;
            }
            $activeSheet->setCellValue($col . $row, $regionData['subtotals']['conversion_ratio']);

            $activeSheet->getStyle('A' . $row . ':' . $column . $row)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF2F2F2'],
                ],
                'font' => ['bold' => true],
            ]);
            $row=$row+2;
        }

        // Grand totals
        $activeSheet->setCellValue('A' . $row, 'Grand Total');
        $activeSheet->setCellValue('B' . $row, $grandTotals['booking_quota']);
        $activeSheet->setCellValue('C' . $row, $grandTotals['booked_body_contouring']);
        $activeSheet->setCellValue('D' . $row, $grandTotals['booked_hifu_facelift']);
        $activeSheet->setCellValue('E' . $row, $grandTotals['booked_trilogy_ice']);
        $activeSheet->setCellValue('F' . $row, $grandTotals['booked_facials']);
        $activeSheet->setCellValue('G' . $row, $grandTotals['booked_others']);
        $activeSheet->setCellValue('H' . $row, $grandTotals['booked']);
        $activeSheet->setCellValue('I' . $row, $grandTotals['booking_quota'] > 0 ? 
            number_format(($grandTotals['booked'] / $grandTotals['booking_quota']) * 100, 2) : 'N/A');
        $activeSheet->setCellValue('J' . $row, $grandTotals['arrived_body_contouring']);
        $activeSheet->setCellValue('K' . $row, $grandTotals['booked_body_contouring'] > 0 ? 
            number_format(($grandTotals['arrived_body_contouring'] / $grandTotals['booked_body_contouring']) * 100, 2) : 'N/A');
        $activeSheet->setCellValue('L' . $row, $grandTotals['arrived_hifu_facelift']);
        $activeSheet->setCellValue('M' . $row, $grandTotals['arrived_trilogy_ice']);
        $activeSheet->setCellValue('N' . $row, $grandTotals['arrived_facials']);
        $activeSheet->setCellValue('O' . $row, $grandTotals['arrived_others']);
        $activeSheet->setCellValue('P' . $row, $grandTotals['arrived']);
        $activeSheet->setCellValue('Q' . $row, $grandTotals['walk_ins']);
        $activeSheet->setCellValue('R' . $row, $grandTotals['arrived_call_center']);
        $activeSheet->setCellValue('S' . $row, $grandTotals['arrived'] > 0 ? 
            number_format(($grandTotals['walk_ins'] / $grandTotals['arrived']) * 100, 2) : 'N/A');
        $activeSheet->setCellValue('T' . $row, $grandTotals['arrived_call_center'] > 0 ? 
            number_format(($grandTotals['walk_ins'] / $grandTotals['arrived_call_center']) * 100, 2) : 'N/A');
        $activeSheet->setCellValue('U' . $row, $grandTotals['booked'] > 0 ? 
            number_format(($grandTotals['arrived_call_center'] / $grandTotals['booked']) * 100, 2) : 'N/A');
        $activeSheet->setCellValue('V' . $row, $grandTotals['booking_quota'] > 0 ? 
            number_format(($grandTotals['arrived'] / $grandTotals['booking_quota']) * 100, 2) : 'N/A');
        
        $col = 'W';
        if (request()->get('is_converted') === 'all' || request()->get('is_converted') === 'converted') {
            $activeSheet->setCellValue($col . $row, $grandTotals['converted']);
            $col++;
        }
        $activeSheet->setCellValue($col . $row, $grandTotals['arrived'] > 0 ? 
            number_format(($grandTotals['converted'] / $grandTotals['arrived']) * 100, 2) : 'N/A');

        $activeSheet->getStyle('A' . $row . ':' . $column . $row)->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFD3D3D3'],
            ],
            'font' => ['bold' => true],
        ]);

        // Apply borders to data cells
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFDDDDDD'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $activeSheet->getStyle('A5:' . $column . $row)->applyFromArray($dataStyle);

        // Auto-size columns
        foreach (range('A', $column) as $columnID) {
            $activeSheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Save the spreadsheet
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="BookingsArrivalsConversions_' . $start_date . '_to_' . $end_date . '.xlsx"');
        header('Cache-Control: max-age=0');

        $Excel_writer->save('php://output');
    }
}