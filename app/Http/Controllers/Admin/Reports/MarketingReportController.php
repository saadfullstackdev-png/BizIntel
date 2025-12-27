<?php

namespace App\Http\Controllers\admin\Reports;

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
use App;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Helpers\dateType;

class MarketingReportController extends Controller
{
    public function report()
    {
        if (!Gate::allows('marketing_reports_manage')) {
            return abort(401);
        }

        $cities = Cities::getActiveSorted(ACL::getUserCities());
        $cities->prepend('Select City', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        $users = User::getUsersleadReport();
        $users->prepend('Created by', '');

        $lead_statuses = LeadStatuses::getLeadStatuses();
        $lead_statuses->prepend('Select Lead Status', '');

        $employees = User::getAllActiveRecords(Auth::User()->account_id);
        if ($employees) {
            $employees = $employees->pluck('full_name', 'id');
            $employees->prepend('Select a Referrer', '');
        } else {
            $employees = array();
        }
        return view('admin.reports.marketing.index', compact('employees', 'cities', 'regions', 'users', 'lead_statuses'));
    }

    public function reportLoad(Request $request)
    {
        if (!Gate::allows('marketing_reports_manage')) {
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
            $leads = Leads::getMarketingReport($request->all());
            $message = null;
        }

        $Cities = Cities::get()->getDictionary();
        $lead_status = LeadStatuses::get()->getDictionary();
        $services = Services::get()->getDictionary();
        $todaydate = Carbon::now()->toDateString();
        $users = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $region = Regions::get()->getDictionary();

        $filters = array();
        $filters['leads'] = $leads;
        $filters['Cities'] = $Cities;
        $filters['lead_status'] = $lead_status;
        $filters['services'] = $services;
        $filters['todaydate'] = $todaydate;
        $filters['users'] = $users;

        switch ($request->get('medium_type')) {
            case 'print':
                return view('admin.reports.marketing.reportprint', compact('leads', 'start_date', 'end_date', 'Cities', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.marketing.reportpdf', compact('leads', 'start_date', 'end_date', 'Cities', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('leadreport', 'landscape');
                break;
            case 'excel':
                self::marketingReportExcel($leads, $start_date, $end_date, $Cities, $lead_status, $services, $todaydate, $users, $region, $message);
                break;
            default:
                return view('admin.reports.marketing.report', compact('leads', 'start_date', 'end_date', 'Cities', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'));
                break;
        }
    }

    /**
     * Mar Report Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function marketingReportExcel($leads, $start_date, $end_date, $Cities, $lead_status, $services, $todaydate, $users, $regions, $message)
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
        $activeSheet->setCellValue('I4', 'Lead Status')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Services')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Created By')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Referred By')->getStyle('L4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A5', '');

        $counter = 6;
        $total = 0;
        if (count($leads)) {
            $count = 1;
            foreach ($leads as $leads) {
                if ($leads->gender == '1') {
                    $gender = 'Male';
                } else if ($leads->gender == '2') {
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
                $activeSheet->setCellValue('I' . $counter, $lead_status[$leads->lead_status_id]->name);
                $activeSheet->setCellValue('J' . $counter, $services[$leads->service_id]->name);
                $activeSheet->setCellValue('K' . $counter, $users[$leads->created_by]->name);
                $activeSheet->setCellValue('L' . $counter, $leads->referred_by ? $users[$leads->referred_by]->name : '');
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
        header('Content-Disposition: attachment;filename="' . 'marketingReportExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }
}
