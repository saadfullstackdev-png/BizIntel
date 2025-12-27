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
use App\Models\Locations;
use Spatie\Permission\Models\Role;
use DB;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Helpers\dateType;


class RevenueBreakupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function report()
    {
        if (!Gate::allows('finance_revenue_breakup_reports_manage')) {
            return abort(401);
        }

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        $roles = DB::table('roles')->get()->pluck('name','id');
        $roles->prepend('Select Role', '');

        $employees = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $operators = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array('' => 'All');

        $users = ($select_All+$employees->toArray()+$operators->toArray());

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('All', '');

        return view('admin.reports.revenuebreakup.index', compact('regions', 'users','roles','locations'));
    }

    /**
     * Load data for reavenue breakup report .
     *
     * @return \Illuminate\Http\Response
     */
    public function reportLoad(Request $request)
    {
        if (!Gate::allows('finance_revenue_breakup_reports_manage')) {
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
            $reportdata = [];
            $message = $date_response['message'];
        } else {
            $reportdata = Revenue::RevenueBreakup($request->all(), Auth::User()->account_id);
            $message = null;
        }

        $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);

        switch ($request->get('medium_type')) {
            case 'pdf':
                $content = view('admin.reports.revenuebreakup.reportpdf',compact('reportdata', 'filters', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Revenue Breakup Report', 'landscape');
                break;
            case 'excel':
                self::actualrevenueExcel($reportdata, $filters, $start_date, $end_date, $message);
                break;
            case 'print':
                return view('admin.reports.revenuebreakup.reportprint', compact('reportdata', 'filters', 'start_date', 'end_date', 'message'));
                break;
            default:
                return view('admin.reports.revenuebreakup.report', compact('reportdata', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }
    }


    /**
     * Actual Revenue Report
     * @param (mixed) $reportData
     * @param (mixed) $filters
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function actualrevenueExcel($reportdata, $filters, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A4', 'Region')->getStyle('A3')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Centre')->getStyle('B3')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Date')->getStyle('C3')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Service Name')->getStyle('D3')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Revenue')->getStyle('E3')->getFont()->setBold(true);

        $counter = 5;
        if (count($reportdata)) {
            $grandtotal = 0;

            foreach ($reportdata as $reportregion) {

                $activeSheet->setCellValue('A' . $counter, $reportregion['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $counter++;

                $regiontotal = 0;

                foreach ($reportregion['centers'] as $reportcentre) {

                    $activeSheet->setCellValue('B' . $counter, $reportcentre['name'])->getStyle('B' . $counter)->getFont()->setBold(true);
                    $counter++;

                    $centertotal = 0;
                    foreach ($reportcentre['date'] as $reportday) {
                        $activeSheet->setCellValue('C' . $counter, ($reportday['Date']) ? \Carbon\Carbon::parse($reportday['Date'], null)->format('M j, Y') : '-');
                        $counter++;
                        $dateservicetotal = 0;
                        foreach($reportday['service'] as $reportservice){
                            $dateservicetotal += $reportservice['total'];
                            $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportservice['service_id'], $filters['services'])) ? $filters['services'][$reportservice['service_id']]->name : '');
                            $activeSheet->setCellValue('E' . $counter, number_format($reportservice['total'],2));
                            $counter++;
                        }

                        $activeSheet->setCellValue('A' . $counter, '');
                        $counter++;

                        $activeSheet->setCellValue('D' . $counter, 'Day Total')->getStyle('D' . $counter)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                        $activeSheet->setCellValue('E' . $counter, number_format($dateservicetotal,2))->getStyle('E' . $counter)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);;
                        $centertotal+=$dateservicetotal;
                        $counter++;
                    }

                    $activeSheet->setCellValue('A' . $counter, '');
                    $counter++;

                    $activeSheet->setCellValue('B' . $counter, 'Centre Total')->getStyle('B' . $counter)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);;
                    $activeSheet->setCellValue('E' . $counter, number_format($centertotal,2))->getStyle('E' . $counter)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);;
                    $regiontotal+=$centertotal;
                    $counter++;

                    $activeSheet->setCellValue('A' . $counter, '');
                    $counter++;
                }
                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                $activeSheet->setCellValue('A' . $counter, 'Region Total')->getStyle('A' . $counter)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);;
                $activeSheet->setCellValue('E' . $counter, number_format($regiontotal,2))->getStyle('E' . $counter)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);;
                $grandtotal+=$regiontotal;
                $counter++;

                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN);;
            $activeSheet->setCellValue('E' . $counter, number_format($grandtotal,2))->getStyle('E' . $counter)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN);;
            $counter++;

            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. 'clientswithcompletedtreatments'.'.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }
}
