<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Exports\PatientExport;
use App\Models\Leads;
use App\Models\LeadSources;
use App\Models\Regions;
use App\Models\Telecomprovider;
use App\Models\Telecomprovidernumber;
use App\Models\Towns;
use App\User;
use App\Models\Cities;
use App\Models\LeadStatuses;
use App\Models\Services;
use App\Helpers\NodesTree;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use App\Helpers\ACL;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;
use Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Helpers\Widgets\TelecomproviderWidget;
use App\Models\Patients;
use Dompdf\Dompdf;
use App;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
use Config;
use App\Models\AppointmentStatuses;
use App\Helpers\Widgets\LocationsWidget;
use App\Models\Doctors;
use App\Models\Locations;
use App\Models\AppointmentTypes;
use App\Helpers\dateType;

class LeadsReportController extends Controller
{
    public function lead()
    {
        if (!Gate::allows('leads_reports_manage')) {
            return abort(401);
        }
        $allserviceslug = Services::where('slug', '=', 'all')->first();

        $cities = Cities::getActiveSorted(ACL::getUserCities());
        $cities->prepend('Select City', '');

        $towns = Towns::getActiveTowns()->pluck('name', 'id');
        $towns->prepend('Select Town', '');

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('Select a Center', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        $employees = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $operators = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array('' => 'Created by');

        $lead_sources = LeadSources::getActiveSorted();
        $lead_sources->prepend('Select a Lead Source', '');

        $users = ($select_All + $employees->toArray() + $operators->toArray());

        $lead_statuses = LeadStatuses::getLeadStatuses();
        $lead_statuses->prepend('Select Lead Status', '');

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);
        $Services = $parentGroups->nodeList;

        foreach ($Services as $key => $ser) {
            if ($key) {
                if ($ser['name'] == $allserviceslug->name) {
                    unset($Services[$key]);
                }
            }
        }

        $leadServices = null;

        $telcomprovider = TelecomproviderWidget::telecomprovider();

        return view('admin.reports.leads.index', compact('lead_sources', 'Services', 'cities', 'regions', 'locations', 'users', 'lead_statuses', 'leadServices', 'telcomprovider', 'towns'));
    }

    /**
     * Load Report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lead_report(Request $request)
    {

        switch ($request->get('report_type')) {
            case 'generalreport':
                return self::generalreport($request);
                break;
            case 'summaryreport':
                return self::summaryreport($request);
                break;
            case 'lead_status_ratio':
                return self::leadstatusratio($request);
                break;
            case 'conversion_rate_at_nationwide_Centers':
                return self::conversionrateatnationwideCenters($request);
                break;
            case 'now_show_report':
                return self::nowshowreport($request);
                break;
            default:
                return self::generalreport($request);
                break;
        }
    }

    /**
     * Load General Report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generalreport(Request $request)
    {
        if (!Gate::allows('leads_reports_general_report')) {
            return abort(401);
        }

        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $leads = [];
            $message = $date_response['message'];
        } else {
            $leads = Leads::getLeadReport($request->all());
            $message = null;
        }

        $leads_sources = LeadSources::all();
        $Cities = Cities::get()->getDictionary();
        $Towns = Towns::get()->getDictionary();
        $Locations = Locations::get()->getDictionary();
        $lead_status = LeadStatuses::get()->getDictionary();
        $services = Services::get()->getDictionary();
        $todaydate = Carbon::now()->toDateString();
        $users = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $region = Regions::get()->getDictionary();

        switch ($request->get('medium_type')) {
            case 'pdf':
                $content = view('admin.reports.leads.reportpdf', compact('leads', 'start_date', 'end_date', 'Cities', 'Towns', 'Locations', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Lead General Report', 'landscape');
                break;
            case 'excel':
                self::leadreportExcel($leads, $start_date, $end_date, $Cities, $Towns, $Locations, $lead_status, $services, $todaydate, $users, $region, $message);
                break;
            case 'print':
                return view('admin.reports.leads.reportprint', compact('leads', 'start_date', 'end_date', 'Cities', 'Towns', 'Locations', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'));
                break;
            default:
                return view('admin.reports.leads.report', compact('leads', 'leads_sources', 'start_date', 'end_date', 'Cities', 'Towns', 'Locations', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'));
                break;
        }
    }

    /**
     * Lead Report Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function leadreportExcel($leads, $start_date, $end_date, $Cities, $Towns, $Location, $lead_status, $services, $todaydate, $users, $regions, $message)
    {

        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xlsx($spreadsheet);  /*----- Excel (Xls) Object*/
        $Excel_writer->setPreCalculateFormulas(false);

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);

        $activeSheet->setCellValue('A2', 'Date')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        $activeSheet->setCellValue('A3', '');

        $activeSheet->setCellValue('A4', 'ID')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Full Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'CNIC')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'DOB')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Email')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Gender')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Region')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'City')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Town')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Center')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Lead Sources')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Lead Status')->getStyle('L4')->getFont()->setBold(true);
        $activeSheet->setCellValue('M4', 'Services')->getStyle('M4')->getFont()->setBold(true);
        $activeSheet->setCellValue('N4', 'Source')->getStyle('N4')->getFont()->setBold(true);
        $activeSheet->setCellValue('O4', 'Created By')->getStyle('O4')->getFont()->setBold(true);

        //$activeSheet->setCellValue('P4', 'Phone')->getStyle('P4')->getFont()->setBold(true);
        //$activeSheet->setCellValue('Q4', 'Address')->getStyle('Q4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A5', '');

        $counter = 6;
        $total = 0;
        if (count($leads)) {
            $count = 1;
            foreach ($leads as $leads) {
                if ($leads->gender == '1') {
                    $gender = 'Male';
                }

        else
        if ($leads->gender == '2') {
            $gender = 'Female';
        } else {
            $gender = '';
        }

        $activeSheet->setCellValue('A' . $counter, $leads->patient_id);
        $activeSheet->setCellValue('B' . $counter, $leads->name);
        $activeSheet->setCellValue('C' . $counter, $leads->cnic);
        $activeSheet->setCellValue('D' . $counter, $leads->dob);
        $activeSheet->setCellValue('E' . $counter, $leads->email);
        $activeSheet->setCellValue('F' . $counter, $gender);
        $activeSheet->setCellValue('G' . $counter, $leads->region_id ? $regions[$leads->region_id]->name : '');
        $activeSheet->setCellValue('H' . $counter, $leads->city_id ? $Cities[$leads->city_id]->name : '');
        $activeSheet->setCellValue('I' . $counter, $leads->town_id ? $Towns[$leads->town_id]->name : '');
        $activeSheet->setCellValue('J' . $counter, $leads->location_id ? $Location[$leads->location_id]->name : '');
        $activeSheet->setCellValue('K' . $counter, $leads->lead_source->name ?? "");
        $activeSheet->setCellValue('L' . $counter, $leads->lead_status_id ? $lead_status[$leads->lead_status_id]->name : '');
        $activeSheet->setCellValue('M' . $counter, $leads->service_id ? $services[$leads->service_id]->name : '');
        $activeSheet->setCellValue('N' . $counter, $leads->source);
        $activeSheet->setCellValue('O' . $counter, $leads->source == 'MOBILE' ? '' : $users[$leads->created_by]->name);

        //$activeSheet->setCellValue('P' . $counter, $leads->phone);
        //$activeSheet->setCellValue('Q' . $counter, $leads->address);

        $counter++;
        }
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Lead Report' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Load Lead Summary Report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function summaryreport(Request $request)
    {
        if (!Gate::allows('leads_reports_summary_report_by_lead_status')) {
            return abort(401);
        }
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $leadstatusreport = [];
            $message = $date_response['message'];
        } else {
            $leadstatusreport = array();
            $leads = Leads::getLeadSummaryReport($request->all());
            $lead_status = LeadStatuses::get();
            foreach ($lead_status as $leadstatus) {
                $count = 0;
                foreach ($leads as $lead) {
                    if ($lead->lead_status_id == $leadstatus->id) {
                        $count++;
                    }
                }
                $leadstatusreport[$leadstatus->id] = array(
                    'id' => $leadstatus->id,
                    'name' => $leadstatus->name,
                    'Total' => $count
                );
            }
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'pdf':
                $content = view('admin.reports.leads.leadsummary.reportpdf', compact('leadstatusreport', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Lead Summar By Lead Status Report', 'landscape');
                break;
            case 'excel':
                self::leadsummmaryreportExcel($leadstatusreport, $start_date, $end_date, $message);
                break;
            case 'print':
                return view('admin.reports.leads.leadsummary.reportprint', compact('leadstatusreport', 'start_date', 'end_date', 'message'));
                break;
            default:
                return view('admin.reports.leads.leadsummary.report', compact('leadstatusreport', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Lead Summary Report Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function leadsummmaryreportExcel($leadstatusreport, $start_date, $end_date, $message)
    {
        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xlsx($spreadsheet);  /*----- Excel (Xls) Object*/
        $Excel_writer->setPreCalculateFormulas(false);

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);

        $activeSheet->setCellValue('A2', 'Date')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        $activeSheet->setCellValue('A3', '');

        $activeSheet->setCellValue('A4', 'Lead Status')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Total')->getStyle('B4')->getFont()->setBold(true);


        $activeSheet->setCellValue('A5', '');

        $counter = 6;

        if (count($leadstatusreport)) {
            foreach ($leadstatusreport as $leads) {

                $activeSheet->setCellValue('A' . $counter, $leads['name']);
                $activeSheet->setCellValue('B' . $counter, $leads['Total']);
                $counter++;
            }
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Lead Summary By Lead Status Excel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Load Lead status Ratio Report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function leadstatusratio(Request $request)
    {
        if (!Gate::allows('leads_reports_lead_status_percentage')) {
            return abort(401);
        }
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $leadstatusreport = [];
            $message = $date_response['message'];
        } else {
            $leadstatusreport = array();

            /*I need same data for lead status ratio so I use same function*/

            $leads = Leads::getLeadSummaryReport($request->all());

            $leadscount = count($leads);

            $lead_status = LeadStatuses::get();

            if (isset($request['lead_status_id']) && $request['lead_status_id']) {
                foreach ($lead_status as $leadstatus) {
                    $count = 0;
                    if ($request['lead_status_id'] == $leadstatus->id) {
                        foreach ($leads as $lead) {
                            if ($lead->lead_status_id == $request['lead_status_id']) {
                                $count++;
                            }
                        }
                        $leadstatusreport[$leadstatus->id] = array(
                            'id' => $leadstatus->id,
                            'name' => $leadstatus->name,
                            'Total' => ($count / $leadscount) * 100
                        );
                    }
                }
            } else {
                foreach ($lead_status as $leadstatus) {
                    $count = 0;
                    foreach ($leads as $lead) {
                        if ($lead->lead_status_id == $leadstatus->id) {
                            $count++;
                        }
                    }
                    $leadstatusreport[$leadstatus->id] = array(
                        'id' => $leadstatus->id,
                        'name' => $leadstatus->name,
                        'Total' => ($count / $leadscount) * 100
                    );
                }
            }
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'pdf':
                $content = view('admin.reports.leads.leadstatusratio.reportpdf', compact('leadstatusreport', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Lead Summar By Lead Status Report', 'landscape');
                break;
            case 'excel':
                self::leadstatusratioExcel($leadstatusreport, $start_date, $end_date, $message);
                break;
            case 'print':
                return view('admin.reports.leads.leadstatusratio.reportprint', compact('leadstatusreport', 'start_date', 'end_date', 'message'));
                break;
            default:
                return view('admin.reports.leads.leadstatusratio.report', compact('leadstatusreport', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Lead Summary Report Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function leadstatusratioExcel($leadstatusreport, $start_date, $end_date, $message)
    {
        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xlsx($spreadsheet);  /*----- Excel (Xls) Object*/
        $Excel_writer->setPreCalculateFormulas(false);

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);

        $activeSheet->setCellValue('A2', 'Date')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        $activeSheet->setCellValue('A3', '');

        $activeSheet->setCellValue('A4', 'Lead Status')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Percentage %')->getStyle('B4')->getFont()->setBold(true);


        $activeSheet->setCellValue('A5', '');

        $counter = 6;

        if (count($leadstatusreport)) {
            foreach ($leadstatusreport as $leads) {

                $activeSheet->setCellValue('A' . $counter, $leads['name']);
                $activeSheet->setCellValue('B' . $counter, number_format(round($leads['Total'], 2), 2) . ' %');
                $counter++;
            }
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Lead Status Ratio Excel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /**
     * Conversation Rate a notion wide Centers
     *
     * @return \Illuminate\Http\Response
     */
    private static function conversionrateatnationwideCenters(Request $request)
    {
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }
        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $reportData = [];
            $message = $date_response['message'];
        } else {
            $reportData = Leads::conversionrateatnationwideCenters($request->all(), $filters, Auth::User()->account_id);
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.staffappointments.report', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.staffappointments.reportprint', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.staffappointments.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Staff Appointment Schedule', 'landscape');
                break;
            case 'excel':
                self::StaffAppointmentScheduleReportExcel($reportData, $filters, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.staffappointments.report', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Now Show Report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function nowshowreport(Request $request)
    {
        if (!Gate::allows('leads_reports_now_show_report')) {
            return abort(401);
        }
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $appointment_info = [];
            $message = $date_response['message'];
        } else {
            $appointment_info = Leads::getNowReport($request->all(), Auth::User()->account_id);
            $message = null;
        }

        $filters['regions'] = Regions::getAll(Auth::User()->account_id)->getDictionary();
        $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_statuses'] = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_types'] = AppointmentTypes::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.noshowlistreport.report', compact('appointment_info', 'start_date', 'end_date', 'filters', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.noshowlistreport.reportpdf', compact('appointment_info', 'start_date', 'end_date', 'filters', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A2', 'landscape');
                return $pdf->stream('Lead General Report', 'landscape');
                break;
            case 'excel':
                self::NowshowreportExcel($appointment_info, $filters, $start_date, $end_date, $message);
                break;
            case 'print':
                return view('admin.reports.noshowlistreport.reportprint', compact('appointment_info', 'start_date', 'end_date', 'filters', 'message'));
                break;
            default:
                return view('admin.reports.noshowlistreport.report', compact('appointment_info', 'start_date', 'end_date', 'filters', 'message'));
                break;
        }
    }

    /**
     * Appointment Reports
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function NowshowreportExcel($reportData, $filters, $start_date, $end_date, $message)
    {
        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xlsx($spreadsheet);  /*----- Excel (Xls) Object*/
        $Excel_writer->setPreCalculateFormulas(false);

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);

        $activeSheet->setCellValue('A2', 'Date')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        $activeSheet->setCellValue('A3', '');
        $activeSheet->setCellValue('B3', '');

        $activeSheet->setCellValue('A4', 'ID')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Client')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Email')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Scheduled')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Doctor')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'City')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Centre')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Treatment/Consultancy')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Status')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Type')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Created At')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Created By')->getStyle('L4')->getFont()->setBold(true);
        $activeSheet->setCellValue('M4', 'Updated By')->getStyle('M4')->getFont()->setBold(true);

        $counter = 5;
        if (count($reportData)) {
            foreach ($reportData as $reportRow) {
                $activeSheet->setCellValue('A' . $counter, $reportRow->patient_id ? $reportRow->patient_id : '');
                $activeSheet->setCellValue('B' . $counter, $reportRow->patient_name ? $reportRow->patient_name : '');
                $activeSheet->setCellValue('C' . $counter, $reportRow->patient_name ? $reportRow->patient_name : '');
                $activeSheet->setCellValue('D' . $counter, ($reportRow->scheduled_date) ? \Carbon\Carbon::parse($reportRow->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->scheduled_time, null)->format('h:i A') : '-');
                $activeSheet->setCellValue('E' . $counter, (array_key_exists($reportRow->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportRow->doctor_id]->name : '');
                $activeSheet->setCellValue('F' . $counter, (array_key_exists($reportRow->city_id, $filters['cities'])) ? $filters['cities'][$reportRow->city_id]->name : '');
                $activeSheet->setCellValue('G' . $counter, (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '');
                $activeSheet->setCellValue('H' . $counter, (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '');
                $activeSheet->setCellValue('I' . $counter, (array_key_exists($reportRow->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportRow->base_appointment_status_id]->name : '');
                $activeSheet->setCellValue('J' . $counter, (array_key_exists($reportRow->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportRow->appointment_type_id]->name : '');
                $activeSheet->setCellValue('K' . $counter, \Carbon\Carbon::parse($reportRow->created_at)->format('M j, Y H:i A'));
                $activeSheet->setCellValue('L' . $counter, (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '');
                $activeSheet->setCellValue('M' . $counter, (array_key_exists($reportRow->updated_by, $filters['users'])) ? $filters['users'][$reportRow->updated_by]->name : '');
                $counter++;
            }
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'No Show List' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }
}
