<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Models\Locations;
use App\Models\Patients;
use App\Models\Services;
use App\Reports\dashboardreport;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Auth;
use Dompdf\Dompdf;

class DashboardReportController extends Controller
{
    public function getRevenueByCenter($period, $medium_type = 'web', $performance = false)
    {

        $start_date = '';
        $end_date = '';

        switch ($period) {
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

        $reportData = dashboardreport::getRevenueByCenter($start_date, $end_date, $performance, Auth::User()->account_id);

        $filters['reportData'] = $reportData;
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['patients'] = Patients::getAll(Auth::User()->account_id)->getDictionary();


        switch ($medium_type) {
            case 'web':
                return view('admin.reports.dashboardreport.revenuebycenter.report', compact('reportData', 'filters', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;

            case 'print':
                return view('admin.reports.dashboardreport.revenuebycenter.reportprint', compact('reportData', 'filters', 'start_date', 'end_date', 'performance'));
                break;

            case 'pdf':
                $content = view('admin.reports.dashboardreport.revenuebycenter.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date', 'performance'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');

                if ($performance) {
                    return $pdf->stream('My Revenue By Centre Report', 'landscape');
                } else {
                    return $pdf->stream('Revenue By Centre Report', 'landscape');
                }
                break;

            case 'excel':
                self::getRevenueByCenterexcel($reportData, $filters, $start_date, $end_date, $performance);
                break;

            default:
                return view('admin.reports.dashboardreport.revenuebycenter.report', compact('report_data', 'start_date', 'end_date', 'medium_type', 'period'));
                break;
        }
    }

    private static function getRevenueByCenterexcel($reportData, $filters, $start_date, $end_date, $performance)
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

        $activeSheet->setCellValue('A4', 'Invoice No.')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Centre')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Service')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Payment Date')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Created By')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Patient')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Service Price')->getStyle('G4')->getFont()->setBold(true);

        $activeSheet->setCellValue('H4', 'Discount Name')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Discount Type')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Discount Price')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Subtotal')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Tax Amount')->getStyle('L4')->getFont()->setBold(true);
        $activeSheet->setCellValue('M4', 'Invoice Price/Total')->getStyle('M4')->getFont()->setBold(true);

        $counter = 5;
        $grandserviceprice = 0;
        $grandtotalservice = 0;

        foreach ($reportData as $reportRow) {

            $grandserviceprice += (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '';
            $grandtotalservice += $reportRow->total_price;

            $activeSheet->setCellValue('A' . $counter, $reportRow->id);
            $activeSheet->setCellValue('B' . $counter, (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '');
            $activeSheet->setCellValue('C' . $counter, (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '');
            $activeSheet->setCellValue('D' . $counter, ($reportRow->created_at) ? \Carbon\Carbon::parse($reportRow->created_at, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->created_at, null)->format('h:i A') : '-');
            $activeSheet->setCellValue('E' . $counter, (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '');
            $activeSheet->setCellValue('F' . $counter, (array_key_exists($reportRow->patient_id, $filters['patients'])) ? $filters['patients'][$reportRow->patient_id]->name : '');
            $activeSheet->setCellValue('G' . $counter, number_format((array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '', 2));

            $activeSheet->setCellValue('H' . $counter, $reportRow->discount_name ? $reportRow->discount_name : '');
            $activeSheet->setCellValue('I' . $counter, $reportRow->discount_type ? $reportRow->discount_type : '');
            $activeSheet->setCellValue('J' . $counter, $reportRow->discount_price ? $reportRow->discount_price : '');
            $activeSheet->setCellValue('K' . $counter, number_format($reportRow->tax_exclusive_serviceprice, 2));
            $activeSheet->setCellValue('L' . $counter, number_format($reportRow->tax_price, 2));

            $activeSheet->setCellValue('M' . $counter, number_format($reportRow->total_price, 2));
            $counter++;
        }

        $activeSheet->setCellValue('A' . $counter, '');
        $counter++;

        $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
        $activeSheet->setCellValue('G' . $counter, number_format($grandserviceprice, 2))->getStyle('G' . $counter)->getFont()->setBold(true);
        $activeSheet->setCellValue('M' . $counter, number_format($grandtotalservice, 2))->getStyle('M' . $counter)->getFont()->setBold(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if ($performance) {
            header('Content-Disposition: attachment;filename="' . 'performancebycentrereport' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        }
        header('Content-Disposition: attachment;filename="' . 'revenuebycentrereport' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /*
     *  Collection Revenue Report centre wise without performance
     */
    public function getCollectionByCenter($medium_type, $performance, $period)
    {
        $start_date = '';
        $end_date = '';

        switch ($period) {
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
        $report_data = dashboardreport::getcollectionrevenue($start_date, $end_date, $performance, Auth::User()->account_id);

        switch ($medium_type) {
            case 'web':
                return view('admin.reports.dashboardreport.colelctionbycenter.report', compact('report_data', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;

            case 'print':
                return view('admin.reports.dashboardreport.colelctionbycenter.reportprint', compact('report_data', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;
            case 'pdf':
                $content = view('admin.reports.dashboardreport.colelctionbycenter.reportpdf', compact('report_data', 'start_date', 'end_date', 'medium_type', 'period', 'performance'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                if ($performance == 'true') {
                    return $pdf->stream('My Collection By Centre Report', 'landscape');
                } else {
                    return $pdf->stream('Collection By Centre Report', 'landscape');
                }
                break;
            case 'excel':
                self::getcollectionByCenterexcel($report_data, $start_date, $end_date, $performance);
                break;
            default:
                return view('admin.reports.dashboardreport.revenuebycenter.report', compact('report_data', 'start_date', 'end_date', 'medium_type', 'period'));
                break;
        }
    }

    public function getcollectionByCenterexcel($report_data, $start_date, $end_date, $performance)
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

        $activeSheet->setCellValue('A4', 'Patient Name')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Transaction type')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Revenue Cash In')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Revenue Card In')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Revenue Bank/Wire In')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Refund/Out')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Cash In Hand')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Created At')->getStyle('H4')->getFont()->setBold(true);


        $counter = 5;

        if (count($report_data)) {

            foreach ($report_data as $reportlocation) {

                if (count($reportlocation['revenue_data'])) {

                    $total_cash_in = 0;
                    $total_card_in = 0;
                    $total_bank_in = 0;
                    $total_refund_out = 0;
                    $balance = 0;

                    $activeSheet->setCellValue('A' . $counter, $reportlocation['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('B' . $counter, $reportlocation['city'])->getStyle('B' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('C' . $counter, $reportlocation['region'])->getStyle('C' . $counter)->getFont()->setBold(true);
                    $counter++;

                    $activeSheet->setCellValue('A' . $counter, '');
                    $counter++;

                    foreach ($reportlocation['revenue_data'] as $reportRow) {

                        $activeSheet->setCellValue('A' . $counter, $reportRow['patient']);
                        $activeSheet->setCellValue('B' . $counter, $reportRow['transtype']);
                        $activeSheet->setCellValue('C' . $counter, number_format(($reportRow['revenue_cash_in'] > 0) ? $reportRow['revenue_cash_in'] : 0));
                        $activeSheet->setCellValue('D' . $counter, number_format(($reportRow['revenue_card_in'] > 0) ? $reportRow['revenue_card_in'] : 0));
                        $activeSheet->setCellValue('E' . $counter, number_format(($reportRow['revenue_bank_in'] > 0) ? $reportRow['revenue_bank_in'] : 0));
                        $activeSheet->setCellValue('F' . $counter, number_format(($reportRow['refund_out'] > 0) ? $reportRow['refund_out'] : 0));
                        $activeSheet->setCellValue('G' . $counter, '');
                        $activeSheet->setCellValue('H' . $counter, $reportRow['created_at']);
                        $counter++;

                        $total_cash_in += $reportRow['revenue_cash_in']>0?$reportRow['revenue_cash_in']:0 ;
                        $total_card_in += $reportRow['revenue_card_in']>0?$reportRow['revenue_card_in']:0;
                        $total_bank_in += $reportRow['revenue_bank_in']>0?$reportRow['revenue_bank_in']:0;
                        $total_refund_out += $reportRow['refund_out']>0?$reportRow['refund_out']:0;


                    }

                    $balance = ($total_cash_in + $total_card_in + $total_bank_in) - $total_refund_out;

                    $activeSheet->setCellValue('A' . $counter, $reportlocation['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('B' . $counter, 'Total')->getStyle('B' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('C' . $counter, number_format($total_cash_in, 2))->getStyle('C' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('D' . $counter, number_format($total_card_in, 2))->getStyle('D' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('E' . $counter, number_format($total_bank_in, 2))->getStyle('E' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('F' . $counter, number_format($total_refund_out, 2))->getStyle('F' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('G' . $counter, number_format($balance, 2))->getStyle('G' . $counter)->getFont()->setBold(true);
                    $counter++;

                    $activeSheet->setCellValue('A3' . $counter, '');
                    $counter++;
                }
            }
        }
        $activeSheet->setCellValue('A' . $counter, '');
        $counter++;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if ($performance == 'true') {
            header('Content-Disposition: attachment;filename="' . 'MyCollectionbyrevenue' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        } else {
            header('Content-Disposition: attachment;filename="' . 'Collectionbyrevenue' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        }
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /*
     * Revenue by service report
     */
    public function getRevenueByService($medium_type, $performance, $period)
    {

        $start_date = '';
        $end_date = '';

        switch ($period) {
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

        $filters = array();
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionaryWithoutAll(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $reportData = dashboardreport::revenuebyservicesales($start_date, $end_date, $performance, Auth::User()->account_id);

        switch ($medium_type) {
            case 'web':
                return view('admin.reports.dashboardreport.revenuebyservice.report', compact('reportData', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;
            case 'print':
                return view('admin.reports.dashboardreport.revenuebyservice.reportprint', compact('reportData', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.dashboardreport.revenuebyservice.reportpdf', compact('reportData', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Daily Employee Stats Summary', 'landscape');
                break;
            case 'excel':
                self::revenuebyserviceexcel($reportData, $start_date, $end_date, $performance);
                break;
            default:
                return view('admin.reports.dashboardreport.revenuebyservice.report', compact('reportData', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;
        }
    }

    /*
     * Revenue By service Excel
     */

    public function revenuebyserviceexcel($reportData, $start_date, $end_date, $performance)
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

        $activeSheet->setCellValue('A3', 'Service')->getStyle('A3')->getFont()->setBold(true);
        $activeSheet->setCellValue('B3', 'Total')->getStyle('B3')->getFont()->setBold(true);

        $counter = 4;
        $total = 0;

        foreach ($reportData as $row) {
            $total = $total + $row['amount'];
            $activeSheet->setCellValue('A' . $counter, $row['name']);
            $activeSheet->setCellValue('B' . $counter, number_format($row['amount'], 2));
            $counter++;
        }

        $activeSheet->setCellValue('A' . $counter, '');
        $activeSheet->setCellValue('B' . $counter, '');
        $counter++;

        $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
        $activeSheet->setCellValue('B' . $counter, number_format($total, 2))->getStyle('B' . $counter)->getFont()->setBold(true);;


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        if ($performance == 'true') {
            header('Content-Disposition: attachment;filename="' . 'MyRevenuebyService' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        } else {
            header('Content-Disposition: attachment;filename="' . 'RevenuebyService' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        }

        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /*
     * Appointment by type
     */
    public function getappointmentbytype($medium_type, $performance, $period)
    {

        $start_date = '';
        $end_date = '';

        switch ($period) {
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

        $filters = array();
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionaryWithoutAll(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');


        $reportData = dashboardreport::Appointmentbytype($start_date, $end_date, $performance, Auth::User()->account_id);

        switch ($medium_type) {
            case 'web':
                return view('admin.reports.dashboardreport.appointmentbytype.report', compact('reportData', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;
            case 'print':
                return view('admin.reports.dashboardreport.appointmentbytype.reportprint', compact('reportData', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.dashboardreport.appointmentbytype.reportpdf', compact('reportData', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Daily Employee Stats Summary', 'landscape');
                break;
            case 'excel':
                self::appointmentbytypeexcel($reportData, $start_date, $end_date, $performance);
                break;
            default:
                return view('admin.reports.dashboardreport.appointmentbytype.report', compact('reportData', 'start_date', 'end_date', 'medium_type', 'period', 'performance'));
                break;
        }
    }

    /*
     * Excel for appointment by status
     */
    public function appointmentbytypeexcel($report_data, $start_date, $end_date, $performance)
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
        $activeSheet->setCellValue('B4', 'Client')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Phone')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Email')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Scheduled')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Doctor')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'City')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Centre')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Treatment/Consultancy')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Status')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Type')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Created At')->getStyle('L4')->getFont()->setBold(true);
        $activeSheet->setCellValue('M4', 'Created By')->getStyle('M4')->getFont()->setBold(true);
        $activeSheet->setCellValue('N4', 'Updated By')->getStyle('N4')->getFont()->setBold(true);
        $activeSheet->setCellValue('O4', 'Rescheduled By')->getStyle('O4')->getFont()->setBold(true);
        $activeSheet->setCellValue('P4', 'Referred By')->getStyle('P4')->getFont()->setBold(true);

        $counter = 5;

        if (count($report_data)) {

            foreach ($report_data as $reporttype) {
                $count = 0;
                $activeSheet->setCellValue('A' . $counter, $reporttype['type_name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $counter++;
                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                foreach ($reporttype['appointment_data'] as $appointmentdata) {

                    $activeSheet->setCellValue('A' . $counter, $appointmentdata['appointment_id']);
                    $activeSheet->setCellValue('B' . $counter, $appointmentdata['patient_name']);
                    $activeSheet->setCellValue('C' . $counter, \App\Helpers\GeneralFunctions::prepareNumber4Call($appointmentdata['patient_phone']));
                    $activeSheet->setCellValue('D' . $counter, $appointmentdata['patient_email']);
                    $activeSheet->setCellValue('E' . $counter, $appointmentdata['scheduled_at']);
                    $activeSheet->setCellValue('F' . $counter, $appointmentdata['doctor_name']);
                    $activeSheet->setCellValue('G' . $counter, $appointmentdata['city']);
                    $activeSheet->setCellValue('H' . $counter, $appointmentdata['centre']);
                    $activeSheet->setCellValue('I' . $counter, $appointmentdata['consultancy']);
                    $activeSheet->setCellValue('J' . $counter, $appointmentdata['status']);
                    $activeSheet->setCellValue('K' . $counter, $appointmentdata['type']);
                    $activeSheet->setCellValue('L' . $counter, $appointmentdata['created_at']);
                    $activeSheet->setCellValue('M' . $counter, $appointmentdata['created_by']);
                    $activeSheet->setCellValue('N' . $counter, $appointmentdata['converted_by']);
                    $activeSheet->setCellValue('O' . $counter, $appointmentdata['rescheduled_by']);
                    $activeSheet->setCellValue('P' . $counter, $appointmentdata['referred_by']);
                    $counter++;

                    $count++;
                }
                $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, number_format($count))->getStyle('B' . $counter)->getFont()->setBold(true);
                $counter++;
                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if ($performance == 'true') {
            header('Content-Disposition: attachment;filename="' . 'MyAppointmentbystatus' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        } else {
            header('Content-Disposition: attachment;filename="' . 'Appointmentbystatus' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        }
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /*
     * Get the report of Appointment by status
     */
    public function getAppointmentsByStatus($period, $medium_type = 'web', $performance = false)
    {

        switch ($period) {
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

        $reportData = dashboardreport::getAppointmentsByStatus($start_date, $end_date, $performance, Auth::user()->account_id);

        switch ($medium_type) {
            case 'web':
                return view('admin.reports.dashboardreport.appointmentbystatus.report', compact('reportData', 'start_date', 'end_date', 'performance', 'medium_type', 'period'));
                break;

            case 'print':
                return view('admin.reports.dashboardreport.appointmentbystatus.reportprint', compact('reportData','start_date', 'end_date','performance','medium_type', 'period'));
                break;

            case 'pdf':
                $pdf = PDF::loadView('admin.reports.dashboardreport.appointmentbystatus.reportpdf', compact('reportData', 'start_date', 'end_date', 'performance', 'medium_type', 'period'));
                $pdf->setPaper('A3', 'landscape');
                if ( $performance === 'true' ){
                    return $pdf->stream('My Appointments By Status Report', 'landscape');
                } else {
                    return $pdf->stream('Appointments By Status Report', 'landscape');
                }

                break;

            case 'excel':
                self::getAppointmentsByStatusExcel( $reportData , $start_date, $end_date, $performance);
                break;

            default:
                return view('admin.reports.dashboardreport.appointmentbystatus.report', compact('reportData', 'start_date', 'end_date', 'performance', 'medium_type', 'period'));
                break;
        }
    }

    /*
     * Get the report of Appointment by status Excel
     */
    private static function getAppointmentsByStatusExcel( $reportData , $start_date, $end_date , $performance)
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
        $activeSheet->setCellValue('B4', 'Client')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Phone')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Email')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Scheduled')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Doctor')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'City')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Centre')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Treatment/Consultancy')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Status')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Type')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Created At')->getStyle('L4')->getFont()->setBold(true);
        $activeSheet->setCellValue('M4', 'Created By')->getStyle('M4')->getFont()->setBold(true);
        $activeSheet->setCellValue('N4', 'Updated By')->getStyle('N4')->getFont()->setBold(true);
        $activeSheet->setCellValue('O4', 'Rescheduled By')->getStyle('O4')->getFont()->setBold(true);
        $activeSheet->setCellValue('P4', 'Referred By')->getStyle('P4')->getFont()->setBold(true);


        $count = 5 ;

        if (count($reportData)){

            foreach ( $reportData as $reportRow ){
                $appointments_count = 0 ;
                $activeSheet->setCellValue('A'.$count, $reportRow['status_name'])->getStyle('A'.$count)->getFont()->setBold(true);

                $count++;

                foreach( $reportRow['appointment_data'] as $appointment ){

                    $activeSheet->setCellValue('A'.$count, $appointment['appointment_id']);
                    $activeSheet->setCellValue('B'.$count, $appointment['patient_name']);
                    $activeSheet->setCellValue('C'.$count, $appointment['patient_phone']);
                    $activeSheet->setCellValue('D'.$count, $appointment['patient_email']);
                    $activeSheet->setCellValue('E'.$count, $appointment['scheduled_at']);
                    $activeSheet->setCellValue('F'.$count, $appointment['doctor_name']);
                    $activeSheet->setCellValue('G'.$count, $appointment['city']);
                    $activeSheet->setCellValue('H'.$count, $appointment['centre']);
                    $activeSheet->setCellValue('I'.$count, $appointment['consultancy']);
                    $activeSheet->setCellValue('J'.$count, $appointment['status']);
                    $activeSheet->setCellValue('K'.$count, $appointment['type']);
                    $activeSheet->setCellValue('L'.$count, $appointment['created_at']);
                    $activeSheet->setCellValue('M'.$count, $appointment['created_by']);
                    $activeSheet->setCellValue('N'.$count, $appointment['converted_by']);
                    $activeSheet->setCellValue('O'.$count, $appointment['rescheduled_by']);
                    $activeSheet->setCellValue('P'.$count, $appointment['referred_by']);

                    $appointments_count++;
                    $count++;

                }

                $activeSheet->setCellValue('A'.$count, 'Total')->getStyle('A'.$count)->getFont()->setBold(true);
                $activeSheet->setCellValue('B'.$count, number_format($appointments_count))->getStyle('B'.$count)->getFont()->setBold(true);

                $count++ ;


            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            if ($performance == 'true') {
                header('Content-Disposition: attachment;filename="' . 'MyAppointmentsByStatus' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
            } else {
                header('Content-Disposition: attachment;filename="' . 'AppointmentsByStatus' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
            }
            header('Cache-Control: max-age=0');
            $Excel_writer->save('php://output');
        }
    }

}
