<?php

namespace App\Http\Controllers\Admin\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Discounts;
use App\Models\InvoiceStatuses;
use App\Models\Regions;
use App\Reports\Finanaces;
use App\Reports\Appointments;
use Carbon\Carbon;
use App\Models\Services;
use App\Helpers\NodesTree;
use App\Models\Locations;
use App\Models\Patients;
use App\Models\Doctors;
use App\User;
use Auth;
use App\Helpers\ACL;
use Barryvdh\DomPDF\Facade as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Models\AppointmentTypes;
use App\Models\Cities;
use App\Models\AppointmentStatuses;
use App\Models\Invoices;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App;
use App\Helpers\dateType;

class LedgerReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function report()
    {
        if (!Gate::allows('finance_ledger_reports_manage')) {
            return abort(401);
        }
        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('All', '');

        return view('admin.reports.Customerpaymentledger.index', compact('locations'));
    }

    /**
     * Load Report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function reportLoad(Request $request)
    {
        switch ($request->get('report_type')) {
            case 'Customer_payment_ledger_all_entries':
                return self::Customerpaymentledgerallentries($request);
                break;
            case 'customer_treatment_package_ledger':
                return self::customertreatmentpackageledger($request);
                break;
            case 'plan_maturity':
                return self::planmaturityreport($request);
                break;
            case 'list_of_advances_as_of_today':
                return self::listofadvancesasoftoday($request);
                break;
            case 'list_of_outstanding_as_of_today':
                return self::listofoutstandingasoftoday($request);
                break;
            case 'Summarized_data_of_Discounts_given_to_the_customer':
                return self::SummarizeddataofDiscountsgiventothecustomer($request);
                break;
            case 'List_of_Clients_who_claimed_refunds':
                return self::ListofClientswhoclaimedrefunds($request);
                break;
            default:
                return self::Customerpaymentledgerallentries($request);
                break;
        }
    }

    /**
     * Customer Payment ledger report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function Customerpaymentledgerallentries(Request $request)
    {
        if (!Gate::allows('finance_ledger_reports_Customer_payment_ledger_all_entries')) {
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
            $reportData = Finanaces::Customerpaymentledgerallentries($request->all(), Auth::User()->account_id);
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.Customerpaymentledger.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.Customerpaymentledger.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.Customerpaymentledger.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Customer Payment Ledger', 'landscape');
                break;
            case 'excel':
                self::CustomerpaymentledgerExcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.Customerpaymentledger.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Customer Payment Ledger Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function CustomerpaymentledgerExcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Patient Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Centre')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Transaction Type')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Cash In')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Cash Out')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Balance')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Created At')->getStyle('H4')->getFont()->setBold(true);

        $counter = 5;
        $grandserviceprice = 0;
        $grandtotalservice = 0;

        if (count($reportData)) {
            foreach ($reportData as $reportRow) {

                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['patient']);
                $activeSheet->setCellValue('C' . $counter, $reportRow['centre']);
                $activeSheet->setCellValue('D' . $counter, $reportRow['transtype']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['cash_in']);
                $activeSheet->setCellValue('F' . $counter, $reportRow['cash_out']);
                $activeSheet->setCellValue('G' . $counter, $reportRow['balance']);
                $activeSheet->setCellValue('H' . $counter, $reportRow['created_at']);
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
        header('Content-Disposition: attachment;filename="' . 'CustomerpaymentledgerExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Center Treatment package ledger Report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function customertreatmentpackageledger(Request $request)
    {
        if (!Gate::allows('finance_ledger_reports_customer_treatment_package_ledger')) {
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
            $reportData = Finanaces::customertreatmentpackageledger($request->all(), Auth::User()->account_id);
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.customertreatmentpackageledger.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.customertreatmentpackageledger.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.customertreatmentpackageledger.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Customer Treatment Plan Ledger', 'landscape');
                break;
            case 'excel':
                self::customertreatmentpackageledgerExcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.customertreatmentpackageledger.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Customer Treatment package Ledger Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function customertreatmentpackageledgerExcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Patient Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Transaction Type')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Cash In')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Cash Out')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Balance')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Created At')->getStyle('G4')->getFont()->setBold(true);

        $counter = 5;
        if (count($reportData)) {
            foreach ($reportData as $reportpackage_advances) {
                $activeSheet->setCellValue('A' . $counter, $reportpackage_advances['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, $reportpackage_advances['patient'])->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, $reportpackage_advances['location'])->getStyle('C' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('D' . $counter, number_format($reportpackage_advances['total_price']))->getStyle('D' . $counter)->getFont()->setBold(true);

                $counter++;
                $count = 0;
                foreach ($reportpackage_advances['children'] as $reportRow) {

                    $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                    $activeSheet->setCellValue('B' . $counter, $reportRow['patient']);
                    $activeSheet->setCellValue('C' . $counter, $reportRow['transtype'])->getStyle('C' . $counter)->getFont();
                    $activeSheet->setCellValue('D' . $counter, $reportRow['cash_in']);
                    $activeSheet->setCellValue('E' . $counter, $reportRow['cash_out']);
                    $activeSheet->setCellValue('F' . $counter, $reportRow['balance']);
                    $activeSheet->setCellValue('G' . $counter, \Carbon\Carbon::parse($reportRow['created_at'])->format('F j,Y h:i A'));
                    $counter++;
                }
                $activeSheet->setCellValue('A' . $counter, '');
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
        header('Content-Disposition: attachment;filename="' . 'customertreatmentpackageledgerExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * list of Advances report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function listofadvancesasoftoday(Request $request)
    {
        if (!Gate::allows('finance_ledger_reports_list_of_advances_as_of_today')) {
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
        if ($request->type) {
            if ($request->type == 'plan') {

                $date_response = dateType::dateTypeDecision($start_date, $end_date);

                if (!$date_response['status']) {
                    $reportData = [];
                    $message = $date_response['message'];
                } else {
                    $reportData = Finanaces::lsitofadvanacesoftodayplan($request->all(), Auth::User()->account_id);
                    $message = null;
                }

                switch ($request->get('medium_type')) {
                    case 'web':
                        return view('admin.reports.lsitofadvanacesoftodayplan.report', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                    case 'print':
                        return view('admin.reports.lsitofadvanacesoftodayplan.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                    case 'pdf':
                        $content = view('admin.reports.lsitofadvanacesoftodayplan.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadHTML($content);
                        $pdf->setPaper('A3', 'landscape');
                        return $pdf->stream('List of advances of Today Plan', 'landscape');

                        break;
                    case 'excel':
                        self::lsitofadvanacesoftodayplanExcel($reportData, $start_date, $end_date, $message);
                        break;
                    default:
                        return view('admin.reports.lsitofadvanacesoftodayplan.report', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                }
            } else {
                $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
                $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['appointment_statuses'] = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['appointment_types'] = AppointmentTypes::getAllRecords(Auth::User()->account_id)->getDictionary();
                $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();

                $date_response = dateType::dateTypeDecision($start_date, $end_date);

                if (!$date_response['status']) {
                    $reportData = [];
                    $message = $date_response['message'];
                } else {
                    $reportData = Finanaces::lsitofadvanacesoftodaynonplan($request->all(), $filters, Auth::User()->account_id);
                    $message = null;
                }

                switch ($request->get('medium_type')) {
                    case 'web':
                        return view('admin.reports.lsitofadvanacesoftodaynonplan.report', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                    case 'print':
                        return view('admin.reports.lsitofadvanacesoftodaynonplan.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                    case 'pdf':
                        $content = view('admin.reports.lsitofadvanacesoftodaynonplan.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadHTML($content);
                        $pdf->setPaper('A3', 'landscape');
                        return $pdf->stream('List of advances of Today Non Plan', 'landscape');
                        break;
                    case 'excel':
                        self::lsitofadvanacesasoftodaynonplanExcel($reportData, $start_date, $end_date, $message);
                        break;
                    default:
                        return view('admin.reports.lsitofadvanacesoftodaynonplan.report', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                }
            }
        } else {
            $reportData = [];
        }
    }

    /**
     * List of advances as of today
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function lsitofadvanacesoftodayplanExcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Patient Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Plan Name')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Is Refund')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Centre')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Total Price')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Advances')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Outstanding')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Use Balance')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Unused Balance')->getStyle('J4')->getFont()->setBold(true);

        $counter = 5;
        $gtotal = 0;
        $gadvances = 0;
        $goutstanding = 0;
        $guse = 0;
        $gunused = 0;

        if (count($reportData)) {
            foreach ($reportData as $reportRow) {

                $gtotal += $reportRow['total_price'];
                $gadvances += $reportRow['advancebalance'];
                $goutstanding += $reportRow['outstandingbalance'];
                $guse += $reportRow['usedbalance'];
                $gunused += $reportRow['unusedbalance'];

                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['patient']);
                $activeSheet->setCellValue('C' . $counter, $reportRow['name']);
                $activeSheet->setCellValue('D' . $counter, $reportRow['is_refund']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['location']);
                $activeSheet->setCellValue('F' . $counter, number_format($reportRow['total_price'], 2));
                $activeSheet->setCellValue('G' . $counter, number_format($reportRow['advancebalance'], 2));
                $activeSheet->setCellValue('H' . $counter, number_format($reportRow['outstandingbalance'], 2));
                $activeSheet->setCellValue('I' . $counter, number_format($reportRow['usedbalance'], 2));
                $activeSheet->setCellValue('J' . $counter, number_format($reportRow['unusedbalance'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('F' . $counter, number_format($gtotal, 2))->getStyle('E' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('G' . $counter, number_format($gadvances, 2))->getStyle('F' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('H' . $counter, number_format($goutstanding, 2))->getStyle('G' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('I' . $counter, number_format($guse, 2))->getStyle('H' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('J' . $counter, number_format($gunused, 2))->getStyle('I' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'lsitofadvanacesasoftodayplanExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }


    /**
     * List of advances as of today for non plans
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function lsitofadvanacesasoftodaynonplanExcel($reportData, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A4', 'Patient ID')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Package Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Email')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Scheduled')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Doctor')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'City')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Centre')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Total Price')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Advances')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Outstanding')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Use Balance')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Unused Balance')->getStyle('L4')->getFont()->setBold(true);

        $counter = 5;
        $gtotal = 0;
        $gadvances = 0;
        $goutstanding = 0;
        $guse = 0;
        $gunused = 0;

        if (count($reportData)) {
            foreach ($reportData as $reportRow) {

                $gtotal += $reportRow['total_price'];
                $gadvances += $reportRow['advancebalance'];
                $goutstanding += $reportRow['outstandingbalance'];
                $guse += $reportRow['usedbalance'];
                $gunused += $reportRow['unusedbalance'];

                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['patient_name']);
                $activeSheet->setCellValue('C' . $counter, $reportRow['email']);
                $activeSheet->setCellValue('D' . $counter, $reportRow['schedule']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['doctor']);
                $activeSheet->setCellValue('F' . $counter, $reportRow['city']);
                $activeSheet->setCellValue('G' . $counter, $reportRow['location']);
                $activeSheet->setCellValue('H' . $counter, number_format($reportRow['total_price'], 2));
                $activeSheet->setCellValue('I' . $counter, number_format($reportRow['advancebalance'], 2));
                $activeSheet->setCellValue('J' . $counter, number_format($reportRow['outstandingbalance'], 2));
                $activeSheet->setCellValue('K' . $counter, number_format($reportRow['usedbalance'], 2));
                $activeSheet->setCellValue('L' . $counter, number_format($reportRow['unusedbalance'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('H' . $counter, number_format($gtotal, 2))->getStyle('H' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('I' . $counter, number_format($gadvances, 2))->getStyle('I' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('J' . $counter, number_format($goutstanding, 2))->getStyle('J' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('K' . $counter, number_format($guse, 2))->getStyle('K' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('L' . $counter, number_format($gunused, 2))->getStyle('L' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'lsitofadvanacesasoftodaynonplanExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * list of Outstanding report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function listofoutstandingasoftoday(Request $request)
    {

        if (!Gate::allows('finance_ledger_reports_list_of_outstanding_as_of_today')) {
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
        if ($request->type) {
            if ($request->type == 'plan') {
                $date_response = dateType::dateTypeDecision($start_date, $end_date);

                if (!$date_response['status']) {
                    $reportData = [];
                    $message = $date_response['message'];
                } else {
                    /*Becasue These both report are same so we same method*/
                    $reportData = Finanaces::lsitofadvanacesoftodayplan($request->all(), Auth::User()->account_id);
                    $message = null;
                }

                switch ($request->get('medium_type')) {
                    case 'web':
                        return view('admin.reports.lsitofoutstandingoftodayplan.report', compact('reportData', 'start_date', 'end_date','message'));
                        break;
                    case 'print':
                        return view('admin.reports.lsitofoutstandingoftodayplan.reportprint', compact('reportData', 'start_date', 'end_date','message'));
                        break;
                    case 'pdf':
                        $content = view('admin.reports.lsitofoutstandingoftodayplan.reportpdf', compact('reportData', 'start_date', 'end_date','message'))->render();
                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadHTML($content);
                        $pdf->setPaper('A3', 'landscape');
                        return $pdf->stream('List of advances of Today Plan', 'landscape');
                        break;
                    case 'excel':
                        self::listofoutstandingasoftodayplanExcel($reportData, $start_date, $end_date, $message);
                        break;
                    default:
                        return view('admin.reports.lsitofoutstandingoftodayplan.report', compact('reportData', 'start_date', 'end_date','message'));
                        break;
                }
            } else {
                $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
                $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['appointment_statuses'] = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['appointment_types'] = AppointmentTypes::getAllRecords(Auth::User()->account_id)->getDictionary();
                $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();

                $date_response = dateType::dateTypeDecision($start_date, $end_date);

                if (!$date_response['status']) {
                    $reportData = [];
                    $message = $date_response['message'];
                } else {
                    /*As we define data same so we use the same function*/
                    $reportData = Finanaces::lsitofadvanacesoftodaynonplan($request->all(), $filters, Auth::User()->account_id);
                    $message = null;
                }

                switch ($request->get('medium_type')) {
                    case 'web':
                        return view('admin.reports.lsitofoutstandingoftodaynonplan.report', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                    case 'print':
                        return view('admin.reports.lsitofoutstandingoftodaynonplan.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                    case 'pdf':
                        $content = view('admin.reports.lsitofoutstandingoftodaynonplan.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadHTML($content);
                        $pdf->setPaper('A3', 'landscape');
                        return $pdf->stream('List of Outstanding of Today non Plan', 'landscape');
                        break;
                    case 'excel':
                        self::listofoutstandingasoftodaynonplanExcel($reportData, $start_date, $end_date, $message);
                        break;
                    default:
                        return view('admin.reports.lsitofoutstandingoftodaynonplan.report', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                }
            }
        } else {
            $reportData = [];
        }
    }

    /**
     * List of advances as of today
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function listofoutstandingasoftodayplanExcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Patient Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Plan Name')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Is Refund')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Centre')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Total Price')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Advances')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Outstanding')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Use Balance')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Unused Balance')->getStyle('J4')->getFont()->setBold(true);

        $counter = 5;
        $gtotal = 0;
        $gadvances = 0;
        $goutstanding = 0;
        $guse = 0;
        $gunused = 0;
        if(count($reportData)){
            foreach ($reportData as $reportRow) {

                $gtotal += $reportRow['total_price'];
                $gadvances += $reportRow['advancebalance'];
                $goutstanding += $reportRow['outstandingbalance'];
                $guse += $reportRow['usedbalance'];
                $gunused += $reportRow['unusedbalance'];

                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['patient']);
                $activeSheet->setCellValue('C' . $counter, $reportRow['name']);
                $activeSheet->setCellValue('D' . $counter, $reportRow['is_refund']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['location']);
                $activeSheet->setCellValue('F' . $counter, number_format($reportRow['total_price'], 2));
                $activeSheet->setCellValue('G' . $counter, number_format($reportRow['advancebalance'], 2));
                $activeSheet->setCellValue('H' . $counter, number_format($reportRow['outstandingbalance'], 2));
                $activeSheet->setCellValue('I' . $counter, number_format($reportRow['usedbalance'], 2));
                $activeSheet->setCellValue('J' . $counter, number_format($reportRow['unusedbalance'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('F' . $counter, number_format($gtotal, 2))->getStyle('E' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('G' . $counter, number_format($gadvances, 2))->getStyle('F' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('H' . $counter, number_format($goutstanding, 2))->getStyle('G' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('I' . $counter, number_format($guse, 2))->getStyle('H' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('J' . $counter, number_format($gunused, 2))->getStyle('I' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'listofoutstandingasoftodayplanExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * List of advances as of today for non plans
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function listofoutstandingasoftodaynonplanExcel($reportData, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A4', 'Patient ID')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Package Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Email')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Scheduled')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Doctor')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'City')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Centre')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Total Price')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Advances')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Outstanding')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Use Balance')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Unused Balance')->getStyle('L4')->getFont()->setBold(true);

        $counter = 5;
        $gtotal = 0;
        $gadvances = 0;
        $goutstanding = 0;
        $guse = 0;
        $gunused = 0;

        if(count($reportData)){
            foreach ($reportData as $reportRow) {

                $gtotal += $reportRow['total_price'];
                $gadvances += $reportRow['advancebalance'];
                $goutstanding += $reportRow['outstandingbalance'];
                $guse += $reportRow['usedbalance'];
                $gunused += $reportRow['unusedbalance'];

                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['patient_name']);
                $activeSheet->setCellValue('C' . $counter, $reportRow['email']);
                $activeSheet->setCellValue('D' . $counter, $reportRow['schedule']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['doctor']);
                $activeSheet->setCellValue('F' . $counter, $reportRow['city']);
                $activeSheet->setCellValue('G' . $counter, $reportRow['location']);
                $activeSheet->setCellValue('H' . $counter, number_format($reportRow['total_price'], 2));
                $activeSheet->setCellValue('I' . $counter, number_format($reportRow['advancebalance'], 2));
                $activeSheet->setCellValue('J' . $counter, number_format($reportRow['outstandingbalance'], 2));
                $activeSheet->setCellValue('K' . $counter, number_format($reportRow['usedbalance'], 2));
                $activeSheet->setCellValue('L' . $counter, number_format($reportRow['unusedbalance'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('H' . $counter, number_format($gtotal, 2))->getStyle('H' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('I' . $counter, number_format($gadvances, 2))->getStyle('I' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('J' . $counter, number_format($goutstanding, 2))->getStyle('J' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('K' . $counter, number_format($guse, 2))->getStyle('K' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('L' . $counter, number_format($gunused, 2))->getStyle('L' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'listofoutstandingasoftodaynonplanExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Summarized data of discounts given to customer
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function SummarizeddataofDiscountsgiventothecustomer(Request $request)
    {

        if (!Gate::allows('finance_ledger_reports_Summarized_data_of_Discounts_given_to_the_customer')) {
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
            $reportData = Finanaces::SummarizeddataofDiscountsgiventothecustomer($request->all(), Auth::User()->account_id);
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.SummarizeddataofDiscountsgiventothecustomer.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.SummarizeddataofDiscountsgiventothecustomer.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.SummarizeddataofDiscountsgiventothecustomer.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Summarized Data of Discounts Given To The Customer', 'landscape');
                break;
            case 'excel':
                self::SummarizeddataofDiscountsgiventothecustomerExcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.SummarizeddataofDiscountsgiventothecustomer.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Summarized data of Discounts given to the customer Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function SummarizeddataofDiscountsgiventothecustomerExcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Package Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Patient Name')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Location')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Is Refund')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Orignal Price')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Discount Price')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Tax Amount')->getStyle('H4')->getFont()->setBold(true);

        $counter = 5;
        $gorignaltotal = 0;
        $gdiscounttotal = 0;
        $grandtaxtotal = 0;

        if(count($reportData)){
            foreach ($reportData as $reportRow) {

                $gorignaltotal += $reportRow['orignal_price'];
                $gdiscounttotal += $reportRow['discount_price'];
                $grandtaxtotal += $reportRow['tax_amt'];


                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['name']);
                $activeSheet->setCellValue('C' . $counter, $reportRow['patient']);
                $activeSheet->setCellValue('D' . $counter, $reportRow['location']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['is_refund']);
                $activeSheet->setCellValue('F' . $counter, number_format($reportRow['orignal_price'], 2));
                $activeSheet->setCellValue('G' . $counter, number_format($reportRow['discount_price'], 2));
                $activeSheet->setCellValue('H' . $counter, number_format($reportRow['tax_amt'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('F' . $counter, number_format($gorignaltotal, 2))->getStyle('F' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('G' . $counter, number_format($gdiscounttotal, 2))->getStyle('G' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('H' . $counter, number_format($grandtaxtotal, 2))->getStyle('H' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'SummarizeddataofDiscountsgiventothecustomerExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * List of Clients who claimed refunds
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function ListofClientswhoclaimedrefunds(Request $request)
    {
        if (!Gate::allows('finance_ledger_reports_List_of_Clients_who_claimed_refunds')) {
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
        if ($request->type) {
            if ($request->type == 'plan') {

                $date_response = dateType::dateTypeDecision($start_date, $end_date);

                if (!$date_response['status']) {
                    $reportData = [];
                    $message = $date_response['message'];
                } else {
                    $reportData = Finanaces::ListofClientswhoclaimedrefunds($request->all(), Auth::User()->account_id);
                    $message = null;
                }
                switch ($request->get('medium_type')) {
                    case 'web':
                        return view('admin.reports.ListofClientswhoclaimedrefunds.report', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                    case 'print':
                        return view('admin.reports.ListofClientswhoclaimedrefunds.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                    case 'pdf':
                        $content = view('admin.reports.ListofClientswhoclaimedrefunds.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadHTML($content);
                        $pdf->setPaper('A4', 'landscape');
                        return $pdf->stream('List Of Clients Who Claimed Refunds', 'landscape');
                        break;
                    case 'excel':
                        self::ListofClientswhoclaimedrefundsExcel($reportData, $start_date, $end_date, $message);
                        break;
                    default:
                        return view('admin.reports.ListofClientswhoclaimedrefunds.report', compact('reportData', 'start_date', 'end_date', 'message'));
                        break;
                }
            } else {
                $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
                $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['appointment_statuses'] = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);
                $filters['appointment_types'] = AppointmentTypes::getAllRecords(Auth::User()->account_id)->getDictionary();
                $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();

                $date_response = dateType::dateTypeDecision($start_date, $end_date);

                if (!$date_response['status']) {
                    $reportData = [];
                    $message = $date_response['message'];
                } else {
                    $reportData = Finanaces::ListofClientswhoclaimedrefundsnonplans($request->all(), $filters, Auth::User()->account_id);
                    $message = null;
                }

                switch ($request->get('medium_type')) {
                    case 'web':
                        return view('admin.reports.ListofClientswhoclaimedrefundsnonplans.report', compact('reportData', 'start_date', 'end_date','message'));
                        break;
                    case 'print':
                        return view('admin.reports.ListofClientswhoclaimedrefundsnonplans.reportprint', compact('reportData', 'start_date', 'end_date','message'));
                        break;
                    case 'pdf':
                        $content = view('admin.reports.ListofClientswhoclaimedrefundsnonplans.reportpdf', compact('reportData', 'start_date', 'end_date','message'))->render();
                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadHTML($content);
                        $pdf->setPaper('A4', 'landscape');
                        return $pdf->stream('List Of Clients Who Claimed Refunds Non Plan', 'landscape');
                        break;
                    case 'excel':
                        self::ListofClientswhoclaimedrefundsnonplansExcel($reportData, $start_date, $end_date, $message);
                        break;
                    default:
                        return view('admin.reports.ListofClientswhoclaimedrefundsnonplans.report', compact('reportData', 'start_date', 'end_date','message'));
                        break;
                }
            }
        } else {
            $reportData = [];
        }
    }

    /**
     * List of client who claimed refunds against plans
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function ListofClientswhoclaimedrefundsExcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Package Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Patient Name')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Centre')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Is Refund')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Total Price')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Refund Amount')->getStyle('G4')->getFont()->setBold(true);

        $counter = 5;
        $gtotal = 0;
        $grefundtotal = 0;

        if(count($reportData)){
            foreach ($reportData as $reportRow) {
                if ($reportRow['refund_amount'] > 0) {
                    $gtotal += $reportRow['total_price'];
                    $grefundtotal += $reportRow['refund_amount'];

                    $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                    $activeSheet->setCellValue('B' . $counter, $reportRow['name']);
                    $activeSheet->setCellValue('C' . $counter, $reportRow['patient']);
                    $activeSheet->setCellValue('D' . $counter, $reportRow['location']);
                    $activeSheet->setCellValue('E' . $counter, $reportRow['is_refund']);
                    $activeSheet->setCellValue('F' . $counter, number_format($reportRow['total_price'], 2));
                    $activeSheet->setCellValue('G' . $counter, number_format($reportRow['refund_amount'], 2));
                    $counter++;
                }
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('F' . $counter, number_format($gtotal, 2))->getStyle('F' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('G' . $counter, number_format($grefundtotal, 2))->getStyle('G' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'ListofClientswhoclaimedrefundsExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * List of client who claimed refunds against non plans
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function ListofClientswhoclaimedrefundsnonplansExcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Patient Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Email')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Doctor')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Service')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Appointment Scheduled')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'City')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Centre')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Total Price')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Refund Amount')->getStyle('J4')->getFont()->setBold(true);

        $counter = 5;
        $gtotal = 0;
        $grefundtotal = 0;
        if(count($reportData)){
            foreach ($reportData as $reportRow) {

                $gtotal += $reportRow['total_price'];
                $grefundtotal += $reportRow['refund_amount'];

                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['patient_name']);
                $activeSheet->setCellValue('C' . $counter, $reportRow['email']);
                $activeSheet->setCellValue('D' . $counter, $reportRow['doctor']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['service']);
                $activeSheet->setCellValue('F' . $counter, $reportRow['schedule']);
                $activeSheet->setCellValue('G' . $counter, $reportRow['city']);
                $activeSheet->setCellValue('H' . $counter, $reportRow['location']);
                $activeSheet->setCellValue('I' . $counter, number_format($reportRow['total_price'], 2));
                $activeSheet->setCellValue('J' . $counter, number_format($reportRow['refund_amount'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('I' . $counter, number_format($gtotal, 2))->getStyle('I' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('J' . $counter, number_format($grefundtotal, 2))->getStyle('J' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'ListofClientswhoclaimedrefundsnonplansExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * list of Outstanding report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function planmaturityreport(Request $request)
    {

        if (!Gate::allows('finance_ledger_reports_plan_maturity')) {
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
            $reportData = Finanaces::planmaturityreport($request->all(), Auth::User()->account_id);
            $message = null;
        }

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.planmaturityreport.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.planmaturityreport.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.planmaturityreport.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Plan Maturity Report', 'landscape');
                break;
            case 'excel':
                self::planmaturityexcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.planmaturityreport.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Plan Maturity Report
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function planmaturityexcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Patient Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Plan Name')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Is Refund')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Centre')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Total Price')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Advances')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Outstanding')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Use Balance')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Unused Balance')->getStyle('J4')->getFont()->setBold(true);

        $counter = 5;
        $gtotal = 0;
        $gadvances = 0;
        $goutstanding = 0;
        $guse = 0;
        $gunused = 0;

        if (count($reportData)) {
            foreach ($reportData as $reportRow) {

                $gtotal += $reportRow['total_price'];
                $gadvances += $reportRow['advancebalance'];
                $goutstanding += $reportRow['outstandingbalance'];
                $guse += $reportRow['usedbalance'];
                $gunused += $reportRow['unusedbalance'];

                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['patient']);
                $activeSheet->setCellValue('C' . $counter, $reportRow['name']);
                $activeSheet->setCellValue('D' . $counter, $reportRow['is_refund']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['location']);
                $activeSheet->setCellValue('F' . $counter, number_format($reportRow['total_price'], 2));
                $activeSheet->setCellValue('G' . $counter, number_format($reportRow['advancebalance'], 2));
                $activeSheet->setCellValue('H' . $counter, number_format($reportRow['outstandingbalance'], 2));
                $activeSheet->setCellValue('I' . $counter, number_format($reportRow['usedbalance'], 2));
                $activeSheet->setCellValue('J' . $counter, number_format($reportRow['unusedbalance'], 2));
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('F' . $counter, number_format($gtotal, 2))->getStyle('E' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('G' . $counter, number_format($gadvances, 2))->getStyle('F' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('H' . $counter, number_format($goutstanding, 2))->getStyle('G' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('I' . $counter, number_format($guse, 2))->getStyle('H' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('J' . $counter, number_format($gunused, 2))->getStyle('I' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'PlanMaturityReport' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }
}
