<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Helpers\ACL;
use App\Helpers\dateType;
use App\Models\Bundles;
use App\Models\Locations;
use App\Reports\Finanaces;
use App\Reports\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Auth;

class PackageReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function report()
    {
        if (!Gate::allows('package_reports_manage')) {
            return abort(401);
        }

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('All', '');

        $packages = Bundles::where('type','=', 'multiple')->get()->pluck('name', 'id');
        $packages->prepend('All', '');

        return view('admin.reports.packages.index', compact('locations','packages'));
    }

    /**
     * Load Report
     */
    public function reportLoad(Request $request)
    {
        switch ($request->get('report_type')) {
            case 'package_sale_count':
                return self::packagesalecountReport($request);
                break;
            default:
                return self::packagesalecountReport($request);
                break;
        }
    }

    /**
     * Wallet Collection Report
     */
    private static function packagesalecountReport(Request $request)
    {
        if (!Gate::allows('package_reports_package_sale_count')) {
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
            $reportData = Package::PackagesalecountReport($request->all(), Auth::User()->account_id);
            $message = null;
        }
        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.packages.package_sale_count.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.packages.package_sale_count.reportprint', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.packages.package_sale_count.reportpdf', compact('reportData', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Package Sale Count', 'landscape');
                break;
            case 'excel':
                self::packageSaleCountReportExcel($reportData, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.packages.package_sale_count.report', compact('reportData', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Wallet Collection report Excel
     */
    private static function packageSaleCountReportExcel($reportData, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A4', 'Location')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Package')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Count')->getStyle('C4')->getFont()->setBold(true);

        $counter = 6;

        $totalCount = 0;

        if (count($reportData)) {
            foreach ($reportData as $reportRow) {

                $totalCount += $reportRow['count'];

                $activeSheet->setCellValue('A' . $counter, $reportRow['location']);
                $activeSheet->setCellValue('B' . $counter, $reportRow['package']);
                $activeSheet->setCellValue('C' . $counter, number_format($reportRow['count'], 2));
                $counter++;
            }

            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('C' . $counter, number_format($totalCount, 2))->getStyle('C' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'PackageSaleCount' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');


    }
}
