<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Helpers\ACL;
use App\Helpers\dateType;
use App\Models\Locations;
use App\Reports\Finanaces;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WalletReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function report()
    {
        if (!Gate::allows('finance_wallet_reports_manage')) {
            return abort(401);
        }

        return view('admin.reports.wallets.index');
    }

    /**
     * Load Report
     */
    public function reportLoad(Request $request)
    {
        switch ($request->get('report_type')) {
            case 'wallet_collection_report':
                return self::walletCollectionReport($request);
                break;
            default:
                return self::walletCollectionReport($request);
                break;
        }
    }

    /**
     * Wallet Collection Report
     */
    private static function walletCollectionReport(Request $request)
    {
        if (!Gate::allows('finance_wallet_reports_wallet_collection_report')) {
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
            $reportData = Finanaces::walletCollectionReport($request->all(), Auth::User()->account_id);
            $message = null;
        }
        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.wallets.walletcollectionreport.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.wallets.walletcollectionreport.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.wallets.walletcollectionreport.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Wallet Collection Report', 'landscape');
                break;
            case 'excel':
                self::walletCollectionReportExcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.wallets.walletcollectionreport.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Wallet Collection report Excel
     */
    private static function walletCollectionReportExcel($reportData, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Patient')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Cash')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Payment Mode')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Created At')->getStyle('E4')->getFont()->setBold(true);

        $counter = 6;

        $totalCash = 0;

        if (count($reportData)) {
            foreach ($reportData as $reportRow) {

                $totalCash += $reportRow['cash'];

                $activeSheet->setCellValue('A' . $counter, $reportRow['patient_id']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['name']);
                $activeSheet->setCellValue('C' . $counter, number_format($reportRow['cash'], 2));
                $activeSheet->setCellValue('D' . $counter, $reportRow['payment_mode']);
                $activeSheet->setCellValue('E' . $counter, $reportRow['created_at']);
                $counter++;
            }

            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('C' . $counter, number_format($totalCash, 2))->getStyle('C' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'WalletCollectionReport' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');


    }


}
