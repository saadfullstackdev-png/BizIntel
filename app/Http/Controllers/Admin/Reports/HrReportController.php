<?php

namespace App\Http\Controllers\Admin\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Exports\PatientExport;
use App\Models\Leads;
use App\Models\Regions;
use App\Models\Telecomprovider;
use App\Models\Telecomprovidernumber;
use App\User;
use App\Models\Cities;
use App\Models\LeadStatuses;
use App\Models\Services;
use App\Helpers\NodesTree;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use App\Helpers\ACL;
use Auth;
use Barryvdh\DomPDF\Facade as PDF;
use Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Helpers\Widgets\TelecomproviderWidget;
use App\Models\Patients;
use App\Reports\Revenue;
use App;
use Spatie\Permission\Models\Role;
use DB;
use App\Models\Locations;
use Config;
use App\Reports\Incentive;
use Dompdf\Dompdf;
use App\Models\Discounts;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Helpers\dateType;


class HrReportController extends Controller
{
    public function report()
    {

        if (!Gate::allows('Hr_reports_manage')) {
            return abort(401);
        }
        $locations = Locations::where([
            ['active', '=', '1'],
            ['slug', '=', 'custom'],
            ['account_id', '=', Auth::User()->account_id]
        ])->get();

        $locations = Locations::getLocationActiveSorted(ACL::getUserCentres());

        $roles = DB::table('roles')->get()->pluck('name', 'id');
        $roles->prepend('Select Role', '');

        $employees = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $operators = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array('' => 'Created by');

        $users = ($select_All + $employees->toArray() + $operators->toArray());

        $applicationusers = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $applicationusers->prepend('Select Application User', '');

        $practitionor = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $practitionor->prepend('Select Application User', '');

        return view('admin.reports.HR_reports.index', compact('users', 'applicationusers', 'practitionor', 'roles', 'locations'));
    }

    public function reportLoad(Request $request)
    {

        switch ($request->get('report_type')) {
            case 'reports_for_calculating_incentives':
                return self::reportsforcalculatingincentives($request);
                break;
            case 'reports_for_calculating_incentives_detail':
                return self::reportsforcalculatingincentivesdetail($request);
                break;
            case 'revenue_generated_by_operators_application_user':
                return self::revenuegeneratedbyoperatorsapplicationuser($request);
                break;
            case 'revenue_generated_by_consultants_practitioner':
                return self::revenuegeneratedbyconsultantspractitioner($request);
                break;
            default:
                return self::reportsforcalculatingincentives($request);
                break;
        }
    }

    /**
     * Report for calculating incentive
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function reportsforcalculatingincentives(Request $request)
    {
        if (!Gate::allows('Hr_reports_reports_for_calculating_incentives')) {
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
            $reportData = [];
            $message = $date_response['message'];
        } else {
            if ($request->location_id == null || $request->role_id == null || $request->search_type == null) {
                $reportData = [];
            } else {
                $reportData = Incentive::reportsforcalculatingincentives($request->all(), Auth::User()->account_id);
            }
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.HR_reports.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.HR_reports.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.HR_reports.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Incentive Report', 'landscape');
                break;
            case 'excel':
                self::reportsforcalculatingincentivesExcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.HR_reports.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Report For Incentive calculation
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function reportsforcalculatingincentivesExcel($reportData, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A4', 'Full Name')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Email')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Phone')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Gender')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Role')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Region')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'City')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Location')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Total Revenue')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Commission %')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Incentive')->getStyle('K4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A5', '');

        $counter = 6;
        $total = 0;
        if (count($reportData)) {
            $total = 0;
            $rtotal = 0;
            foreach ($reportData as $user) {
                $total += $user['Incentive'];
                $rtotal += $user['TotalRevenue'];
                $activeSheet->setCellValue('A' . $counter, $user['name']);
                $activeSheet->setCellValue('B' . $counter, $user['email']);
                $activeSheet->setCellValue('C' . $counter, $user['phone']);
                $activeSheet->setCellValue('D' . $counter, $user['gender']);
                $activeSheet->setCellValue('E' . $counter, $user['Role']);
                $activeSheet->setCellValue('F' . $counter, $user['Region']);
                $activeSheet->setCellValue('G' . $counter, $user['City']);
                $activeSheet->setCellValue('H' . $counter, $user['Location']);
                $activeSheet->setCellValue('I' . $counter, number_format($user['TotalRevenue'], 2));
                $activeSheet->setCellValue('J' . $counter, $user['commission']);
                $activeSheet->setCellValue('K' . $counter, number_format($user['Incentive'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('I' . $counter, number_format($rtotal, 2))->getStyle('I' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('K' . $counter, number_format($total, 2))->getStyle('K' . $counter)->getFont()->setBold(true);
            $counter++;
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Incentive Report' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Report for calculating incentive detail
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function reportsforcalculatingincentivesdetail(Request $request)
    {
        if (!Gate::allows('Hr_reports_reports_for_calculating_incentives_detail')) {
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
            $reportData = [];
            $message = $date_response['message'];
        } else {
            if ($request->location_id == null || $request->role_id == null || $request->search_type == null) {
                $reportData = [];
            } else {
                $reportData = Incentive::reportsforcalculatingincentivesdetail($request->all(), Auth::User()->account_id);
            }
            $message = null;
        }

        $filters['reportData'] = $reportData;
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionaryWithoutAll(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['patients'] = Patients::getAll(Auth::User()->account_id)->getDictionary();
        $filters['discounts'] = Discounts::getDiscountforreport(session('account_id'))->getDictionary();

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.HR_reports.HR_reportsdetail.report', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.HR_reports.HR_reportsdetail.reportprint', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.HR_reports.HR_reportsdetail.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Incentive Report', 'landscape');
                break;
            case 'excel':
                self::reportsforcalculatingincentivesdetailExcel($reportData, $filters, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.HR_reports.HR_reportsdetail.report', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Report For Incentive detail calculation
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function reportsforcalculatingincentivesdetailExcel($reportData, $filters, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A4', 'Full Name')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Email')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Phone')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Gender')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Role')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Region')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'City')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Location')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Total Revenue')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Commission %')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Incentive')->getStyle('K4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A5', '');

        $counter = 6;
        $total = 0;
        if (count($reportData)) {
            $total = 0;
            $rtotal = 0;
            foreach ($reportData as $user) {

                $total += $user['Incentive'];
                $rtotal += $user['TotalRevenue'];

                $activeSheet->setCellValue('A' . $counter, $user['name']);
                $activeSheet->setCellValue('B' . $counter, $user['email']);
                $activeSheet->setCellValue('C' . $counter, $user['phone']);
                $activeSheet->setCellValue('D' . $counter, $user['gender']);
                $activeSheet->setCellValue('E' . $counter, $user['Role']);
                $activeSheet->setCellValue('F' . $counter, $user['Region']);
                $activeSheet->setCellValue('G' . $counter, $user['City']);
                $activeSheet->setCellValue('H' . $counter, $user['Location']);
                $activeSheet->setCellValue('I' . $counter, number_format($user['TotalRevenue'], 2));
                $activeSheet->setCellValue('J' . $counter, $user['commission']);
                $activeSheet->setCellValue('K' . $counter, number_format($user['Incentive'], 2));
                $counter++;

                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                $activeSheet->setCellValue('A' . $counter, 'Invoice No.')->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, 'Service')->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, 'Payment Date')->getStyle('C' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('D' . $counter, 'Created By')->getStyle('D' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('E' . $counter, 'Patient')->getStyle('E' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('F' . $counter, 'Service Price')->getStyle('F' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('G' . $counter, 'Discount Name')->getStyle('G' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('H' . $counter, 'Discount Type')->getStyle('H' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('I' . $counter, 'Discount Amount')->getStyle('I' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('J' . $counter, 'Invoice Price')->getStyle('J' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('K' . $counter, 'Centre')->getStyle('K' . $counter)->getFont()->setBold(true);
                $counter++;

                $grandserviceprice = 0;
                $grandtotalservice = 0;

                foreach ($user['detail'] as $reportRow) {


                    $grandserviceprice += (array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->price : '';
                    $grandtotalservice += $reportRow['total_price'];

                    $activeSheet->setCellValue('A' . $counter, $reportRow['id']);
                    $activeSheet->setCellValue('B' . $counter, (array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->name : '');
                    $activeSheet->setCellValue('C' . $counter, ($reportRow['created_at']) ? \Carbon\Carbon::parse($reportRow['created_at'], null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow['created_at'], null)->format('h:i A') : '-');
                    $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportRow['created_by'], $filters['users'])) ? $filters['users'][$reportRow['created_by']]->name : '');
                    $activeSheet->setCellValue('E' . $counter, (array_key_exists($reportRow['patient_id'], $filters['patients'])) ? $filters['patients'][$reportRow['patient_id']]->name : '');
                    $activeSheet->setCellValue('F' . $counter, number_format((array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->price : '', 2));
                    $activeSheet->setCellValue('G' . $counter, (array_key_exists($reportRow['discount_id'], $filters['discounts'])) ? $filters['discounts'][$reportRow['discount_id']]->name : '-');
                    $activeSheet->setCellValue('H' . $counter, $reportRow['discount_type'] == null ? '-' : $reportRow['discount_type']);
                    $activeSheet->setCellValue('I' . $counter, $reportRow['discount_price'] == null ? '-' : $reportRow['discount_price']);
                    $activeSheet->setCellValue('J' . $counter, number_format($reportRow['total_price'], 2));
                    $activeSheet->setCellValue('K' . $counter, (array_key_exists($reportRow['location_id'], $filters['locations'])) ? $filters['locations'][$reportRow['location_id']]->name : '');

                    $counter++;
                }
                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('F' . $counter, number_format($grandserviceprice, 2))->getStyle('F' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('J' . $counter, number_format($grandtotalservice, 2))->getStyle('J' . $counter)->getFont()->setBold(true);
                $counter++;

                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;
            }

            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('I' . $counter, number_format($rtotal, 2))->getStyle('I' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('K' . $counter, number_format($total, 2))->getStyle('K' . $counter)->getFont()->setBold(true);
            $counter++;
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Incentive Report' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Report for calculating incentive for Application user
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function revenuegeneratedbyoperatorsapplicationuser(Request $request)
    {
        if (!Gate::allows('Hr_reports_revenue_generated_by_operators_application_user')) {
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
            $reportData = [];
            $message = $date_response['message'];
        } else {
            if ($request->location_id == null || $request->search_type == null) {
                $reportData = [];
            } else {
                $reportData = Incentive::revenuegeneratedbyoperatorsapplicationuser($request->all(), Auth::User()->account_id);
            }
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.revenuegeneratedbyoperatorsapplicationuser.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.revenuegeneratedbyoperatorsapplicationuser.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.revenuegeneratedbyoperatorsapplicationuser.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Revenue Generated By Operators Application User', 'landscape');
                break;
            case 'excel':
                self::revenuegeneratedbyoperatorsapplicationuserExcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.revenuegeneratedbyoperatorsapplicationuser.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Revenue Generated by Operators Application User Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function revenuegeneratedbyoperatorsapplicationuserExcel($reportData, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A4', 'Full Name')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Email')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Phone')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Gender')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Region')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'City')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Location')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Total Revenue')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Commission %')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Incentive')->getStyle('K4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A5', '');

        $counter = 6;
        $total = 0;
        if (count($reportData)) {
            $total = 0;
            $rtotal = 0;
            foreach ($reportData as $user) {
                $total += $user['Incentive'];
                $rtotal += $user['TotalRevenue'];
                $activeSheet->setCellValue('A' . $counter, $user['name']);
                $activeSheet->setCellValue('B' . $counter, $user['email']);
                $activeSheet->setCellValue('C' . $counter, $user['phone']);
                $activeSheet->setCellValue('D' . $counter, $user['gender']);
                $activeSheet->setCellValue('E' . $counter, $user['Region']);
                $activeSheet->setCellValue('F' . $counter, $user['City']);
                $activeSheet->setCellValue('G' . $counter, $user['Location']);
                $activeSheet->setCellValue('H' . $counter, number_format($user['TotalRevenue'], 2));
                $activeSheet->setCellValue('I' . $counter, $user['commission']);
                $activeSheet->setCellValue('J' . $counter, number_format($user['Incentive'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('H' . $counter, number_format($rtotal, 2))->getStyle('H' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('J' . $counter, number_format($total, 2))->getStyle('J' . $counter)->getFont()->setBold(true);
            $counter++;
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Revenue Generated by Operator (Application User)' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Report for calculating incentive for doctor
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function revenuegeneratedbyconsultantspractitioner(Request $request)
    {
        if (!Gate::allows('Hr_reports_revenue_generated_by_consultants_practitioner')) {
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
            $reportData = [];
            $message = $date_response['message'];
        } else {
            if ($request->location_id == null || $request->search_type == null) {
                $reportData = [];
            } else {
                $reportData = Incentive::revenuegeneratedbyconsultantspractitioner($request->all(), Auth::User()->account_id);
            }
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.revenuegeneratedbyconsultantspractitioner.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.revenuegeneratedbyconsultantspractitioner.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.revenuegeneratedbyconsultantspractitioner.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Revenue Generated By Operators Application User', 'landscape');
                break;
            case 'excel':
                self::revenuegeneratedbyconsultantspractitionerExcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.revenuegeneratedbyconsultantspractitioner.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Revenue Generated by Operators Application User Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function revenuegeneratedbyconsultantspractitionerExcel($reportData, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A4', 'Full Name')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Email')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Phone')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Gender')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Region')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'City')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Location')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Total Revenue')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Commission %')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Incentive')->getStyle('K4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A5', '');

        $counter = 6;
        $total = 0;
        if (count($reportData)) {
            $total = 0;
            $rtotal = 0;
            foreach ($reportData as $user) {
                $total += $user['Incentive'];
                $rtotal += $user['TotalRevenue'];
                $activeSheet->setCellValue('A' . $counter, $user['name']);
                $activeSheet->setCellValue('B' . $counter, $user['email']);
                $activeSheet->setCellValue('C' . $counter, $user['phone']);
                $activeSheet->setCellValue('D' . $counter, $user['gender']);
                $activeSheet->setCellValue('E' . $counter, $user['Region']);
                $activeSheet->setCellValue('F' . $counter, $user['City']);
                $activeSheet->setCellValue('G' . $counter, $user['Location']);
                $activeSheet->setCellValue('H' . $counter, number_format($user['TotalRevenue'], 2));
                $activeSheet->setCellValue('I' . $counter, $user['commission']);
                $activeSheet->setCellValue('J' . $counter, number_format($user['Incentive'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('H' . $counter, number_format($rtotal, 2))->getStyle('H' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('J' . $counter, number_format($total, 2))->getStyle('J' . $counter)->getFont()->setBold(true);
            $counter++;
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Revenue Generated by Consultant (Practitioner)' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }
}
