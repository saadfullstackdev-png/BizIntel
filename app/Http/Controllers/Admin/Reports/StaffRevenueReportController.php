<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Models\Discounts;
use App\Models\InvoiceStatuses;
use App\Models\Regions;
use App\Reports\Finanaces;
//use App\Reports\Appointments;
use App\Reports\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Helpers\NodesTree;
use App\Models\Locations;
use App\Models\Patients;
use App\Models\Doctors;
use App\User;
use Auth;
use App\Helpers\ACL;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\App;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Models\AppointmentTypes;
use App\Models\Cities;
use App\Models\AppointmentStatuses;
use App\Models\Invoices;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Helpers\dateType;

class StaffRevenueReportController extends Controller
{
    /**
     *
     */
    public function report()
    {
        if (!Gate::allows('staff_revenue_reports_manage')) {
            return abort(401);
        }

        $allServiceSlug = Services::where('slug', '=', 'all')->first();
        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);
        $services = $parentGroups->nodeList;

        foreach ($services as $key => $ser) {
            if ($key) {
                if ($ser['name'] == $allServiceSlug->name) {
                    unset($services[$key]);
                }
            }
        }

        $employees = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $operators = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array('' => 'Select Employee');

        $users = ($select_All + $employees->toArray() + $operators->toArray());

        $patients = Patients::getAll(Auth::User()->account_id);

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('All', '');

        $appointment_types = AppointmentTypes::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $appointment_types->prepend('All', '');

        return view('admin.reports.staff_reports.staff_revenue_report.index', compact('patients', 'locations', 'services', 'users', 'appointment_types'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function reportLoad(Request $request)
    {
        switch ($request->get('report_type')) {
            case 'center_performance_stats_by_revenue':
                return self::centerStaffPerformanceStatsByRevenue($request);
                break;
            case 'center_performance_stats_by_service_type':
                return self::centerPerformanceStatsByServiceType($request);
                break;
            default:
                return self::centerStaffPerformanceStatsByRevenue($request);
                break;
        }
    }

    /**
     * Center Performance status by revenue
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function centerStaffPerformanceStatsByRevenue(Request $request)
    {

        if (!Gate::allows('staff_revenue_reports_center_performance_stats_by_revenue')) {
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

        $filters = array();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
        $filters['regions'] = Regions::getAll(Auth::User()->account_id)->getDictionary();
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
            $reportData = Staff::centerStaffPerformanceStatsByRevenue($request->all(), $filters);

            $invoiceStatus = InvoiceStatuses::where('slug', '=', 'paid')->first();

            foreach ($reportData as $key1 => $report_Data) {

                foreach ($report_Data['records'] as $key2 => $report_row) {

                    foreach ($report_row['appointments'] as $thisRecordRow) {
                        $Salestotal = Invoices::where([
                            ['appointment_id', '=', $thisRecordRow->id],
                            ['invoice_status_id', '=', $invoiceStatus->id]
                        ])->first();
                        if ($Salestotal) {
                            $reportData[$key1]['records'][$key2]['appointments'][$thisRecordRow->id]['Salestotal'] = $Salestotal->total_price;
                        } else {
                            $reportData[$key1]['records'][$key2]['appointments'][$thisRecordRow->id]['Salestotal'] = 0;
                        }
                    }
                }
            }
            $message = null;
        }

        $filters['reportData'] = $reportData;

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.staff_reports.staff_revenue_report.report', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'print':
                return view('admin.reports.staff_reports.staff_revenue_report.reportprint', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.staff_reports.staff_revenue_report.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A2', 'landscape');
                return $pdf->stream('Center Performance Stats By Revenue Report', 'landscape');
                break;
            case 'excel':
                self::centerStaffPerformanceStatsByRevenueExcel($reportData, $filters, $start_date, $end_date, $message);
                break;
            default:
                return view('admin.reports.staff_reports.staff_revenue_report.report', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Centre performance states by revenue Excel
     * @param (mixed) $reportData
     * @param (mixed) $filters
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function centerStaffPerformanceStatsByRevenueExcel($reportData, $filters, $start_date, $end_date, $message)
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

        $activeSheet->setCellValue('A3', 'ID')->getStyle('A3')->getFont()->setBold(true);
        $activeSheet->setCellValue('B3', 'Client Name')->getStyle('B3')->getFont()->setBold(true);
        $activeSheet->setCellValue('C3', 'Created At')->getStyle('C3')->getFont()->setBold(true);
        $activeSheet->setCellValue('D3', 'Doctor')->getStyle('D3')->getFont()->setBold(true);
        $activeSheet->setCellValue('E3', 'Service')->getStyle('E3')->getFont()->setBold(true);
        $activeSheet->setCellValue('F3', 'Email')->getStyle('F3')->getFont()->setBold(true);
        $activeSheet->setCellValue('G3', 'Scheduled')->getStyle('G3')->getFont()->setBold(true);
        $activeSheet->setCellValue('H3', 'City')->getStyle('H3')->getFont()->setBold(true);
        $activeSheet->setCellValue('I3', 'Centre')->getStyle('I3')->getFont()->setBold(true);
        $activeSheet->setCellValue('J3', 'Status')->getStyle('J3')->getFont()->setBold(true);
        $activeSheet->setCellValue('K3', 'Type')->getStyle('K3')->getFont()->setBold(true);
        $activeSheet->setCellValue('L3', 'Service Price')->getStyle('L3')->getFont()->setBold(true);
        $activeSheet->setCellValue('M3', 'Invoice Price')->getStyle('M3')->getFont()->setBold(true);
        $activeSheet->setCellValue('N3', 'Created By')->getStyle('N3')->getFont()->setBold(true);
        $counter = 4;
        if (count($reportData)) {
            $count = 0;
            $salesGrandTotal = 0;
            $serviceGrandTotal = 0;
            $grandcount = 0;
            foreach ($reportData as $reportpackagedata) {
                $counter++;
                $activeSheet->setCellValue('A' . $counter, $reportpackagedata['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, $reportpackagedata['region'])->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, $reportpackagedata['city'])->getStyle('C' . $counter)->getFont()->setBold(true);
                $counter++;

                $count = 0;
                $salestotal = 0;
                $servicetotal = 0;

                foreach ($reportpackagedata['records'] as $reportRow) {
                    //dd($reportRow);
                    $counter++;
                    $activeSheet->setCellValue('A' . $counter, $reportRow['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                    $counter++;

                    $thisDoctorServicePrice = 0;
                    $thisDoctorInvoicePrice = 0;

                    foreach ($reportRow['appointments'] as $thisAppointment) {
                        $serviceprice = (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                        $servicetotal += $serviceprice;
                        $thisDoctorServicePrice += $serviceprice;

                        $salestotal += $thisAppointment->Salestotal;
                        $thisDoctorInvoicePrice += $thisAppointment->Salestotal;

                        $activeSheet->setCellValue('A' . $counter, $thisAppointment->patient_id)->getStyle('A' . $counter)->getFont();
                        $activeSheet->setCellValue('B' . $counter, $thisAppointment->patient->name)->getStyle('B' . $counter)->getFont();
                        $activeSheet->setCellValue('C' . $counter, \Carbon\Carbon::parse($thisAppointment->created_at)->format('M j, Y H:i A'))->getStyle('C' . $counter);
                        $activeSheet->setCellValue('D' . $counter, (array_key_exists($thisAppointment->doctor_id, $filters['doctors'])) ? $filters['doctors'][$thisAppointment->doctor_id]->name : '');
                        $activeSheet->setCellValue('E' . $counter, (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->name : '');
                        $activeSheet->setCellValue('F' . $counter, $thisAppointment->patient->email);
                        $activeSheet->setCellValue('G' . $counter, ($thisAppointment->scheduled_date) ? \Carbon\Carbon::parse($thisAppointment->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($thisAppointment->scheduled_time, null)->format('h:i A') : '-');
                        $activeSheet->setCellValue('H' . $counter, (array_key_exists($thisAppointment->city_id, $filters['cities'])) ? $filters['cities'][$thisAppointment->city_id]->name : '');
                        $activeSheet->setCellValue('I' . $counter, (array_key_exists($thisAppointment->location_id, $filters['locations'])) ? $filters['locations'][$thisAppointment->location_id]->name : '');
                        $activeSheet->setCellValue('J' . $counter, (array_key_exists($thisAppointment->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$thisAppointment->base_appointment_status_id]->name : '');
                        $activeSheet->setCellValue('K' . $counter, (array_key_exists($thisAppointment->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$thisAppointment->appointment_type_id]->name : '');
                        $activeSheet->setCellValue('L' . $counter, number_format($serviceprice, 2));
                        $activeSheet->setCellValue('M' . $counter, number_format($thisAppointment->Salestotal, 2));
                        $activeSheet->setCellValue('N' . $counter, (array_key_exists($thisAppointment->created_by, $filters['users'])) ? $filters['users'][$thisAppointment->created_by]->name : '');
                        $counter++;
                        $grandcount++;
                        $count++;
                    }
                    $count++;

                    $activeSheet->setCellValue('A' . $counter, $reportRow['name'])->getStyle('A' . $counter)->getFont();
                    $activeSheet->setCellValue('B' . $counter, 'Appointments: ' . count($reportRow['appointments']))->getStyle('B' . $counter)->getFont();
                    $activeSheet->setCellValue('L' . $counter, number_format($thisDoctorServicePrice, 2))->getStyle('L' . $counter)->getFont();
                    $activeSheet->setCellValue('M' . $counter, number_format($thisDoctorInvoicePrice, 2))->getStyle('M' . $counter)->getFont();

                    $count++;
                }
                $count++;
                $serviceGrandTotal += $servicetotal;
                $salesGrandTotal += $salestotal;

                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                $activeSheet->setCellValue('A' . $counter, $reportpackagedata['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, 'Total');
                $activeSheet->setCellValue('C' . $counter, $count)->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('L' . $counter, number_format($servicetotal, 2))->getStyle('L' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('M' . $counter, number_format($salestotal, 2))->getStyle('M' . $counter)->getFont()->setBold(true);
                $counter++;

                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('B' . $counter, 'Grand Total');
            $activeSheet->setCellValue('C' . $counter, $grandcount)->getStyle('B' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('L' . $counter, number_format($serviceGrandTotal, 2))->getStyle('L' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('M' . $counter, number_format($salesGrandTotal, 2))->getStyle('M' . $counter)->getFont()->setBold(true);
            $counter++;

        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Staff_Revenue_Centre_Wise' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Center Performance status by service type
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function centerPerformanceStatsByServiceType(Request $request)
    {

        if (!Gate::allows('staff_revenue_reports_center_performance_stats_by_service_type')) {
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
        $reportName = "Staff Revenue by Service Type";
        $filters = array();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
        $filters['regions'] = Regions::getAll(Auth::User()->account_id)->getDictionary();
        $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_statuses'] = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_types'] = AppointmentTypes::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['patients'] = Patients::getAll(Auth::User()->account_id)->getDictionary();

        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $reportData = [];
            $message = $date_response['message'];
        } else {
            $reportData = Staff::centerPerformanceStatsByServices($request->all(), $filters);
            $invoiceStatus = InvoiceStatuses::where('slug', '=', 'paid')->first();

            foreach ($reportData as $key1 => $report_Data) {
                foreach ($report_Data['records'] as $key2 => $report_row) {
                    foreach ($report_row['appointments'] as $thisRecordRow) {
                        $Salestotal = Invoices::where([
                            ['appointment_id', '=', $thisRecordRow->id],
                            ['invoice_status_id', '=', $invoiceStatus->id]
                        ])->first();
                        if ($Salestotal) {
                            $reportData[$key1]['records'][$key2]['appointments'][$thisRecordRow->id]['Salestotal'] = $Salestotal->total_price;
                        } else {
                            $reportData[$key1]['records'][$key2]['appointments'][$thisRecordRow->id]['Salestotal'] = 0;
                        }
                    }
                }
            }
            $message = null;
        }

        $filters['reportData'] = $reportData;

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.staff_reports.staff_service_revenue_report.report', compact('reportData', 'filters', 'start_date', 'end_date', 'reportName', 'message'));
                break;
            case 'print':
                return view('admin.reports.staff_reports.staff_service_revenue_report.reportprint', compact('reportData', 'filters', 'start_date', 'end_date', 'reportName', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.staff_reports.staff_service_revenue_report.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date', 'reportName', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Staff Revenue by Service Type', 'landscape');
                break;
            case 'excel':
                self::centerPerformanceStatsByServiceTypeExcel($reportData, $filters, $start_date, $end_date, $reportName, $message);
                break;
            default:
                return view('admin.reports.staff_reports.staff_service_revenue_report.report', compact('reportData', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }

    }

    /**
     * Centre performance states by service type Excel
     * @param (mixed) $reportData
     * @param (mixed) $filters
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function centerPerformanceStatsByServiceTypeExcel($reportData, $filters, $start_date, $end_date, $reportName, $message)
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

        $activeSheet->setCellValue('A3', 'ID')->getStyle('A3')->getFont()->setBold(true);
        $activeSheet->setCellValue('B3', 'Client Name')->getStyle('B3')->getFont()->setBold(true);
        $activeSheet->setCellValue('C3', 'Created At')->getStyle('C3')->getFont()->setBold(true);
        $activeSheet->setCellValue('D3', 'Doctor')->getStyle('D3')->getFont()->setBold(true);
        $activeSheet->setCellValue('E3', 'Service')->getStyle('E3')->getFont()->setBold(true);
        $activeSheet->setCellValue('F3', 'Email')->getStyle('F3')->getFont()->setBold(true);
        $activeSheet->setCellValue('G3', 'Scheduled')->getStyle('G3')->getFont()->setBold(true);
        $activeSheet->setCellValue('H3', 'City')->getStyle('H3')->getFont()->setBold(true);
        $activeSheet->setCellValue('I3', 'Centre')->getStyle('I3')->getFont()->setBold(true);
        $activeSheet->setCellValue('J3', 'Status')->getStyle('J3')->getFont()->setBold(true);
        $activeSheet->setCellValue('K3', 'Type')->getStyle('K3')->getFont()->setBold(true);
        $activeSheet->setCellValue('L3', 'Service Price')->getStyle('L3')->getFont()->setBold(true);
        $activeSheet->setCellValue('M3', 'Invoice Price')->getStyle('M3')->getFont()->setBold(true);
        $activeSheet->setCellValue('N3', 'Created By')->getStyle('N3')->getFont()->setBold(true);

        $counter = 5;
        if (count($reportData)) {
            $grandServicePrice = 0;
            $grandTotalService = 0;
            $servicetotal = 0;
            $grandCount = 0;
            foreach ($reportData as $reportRow) {
                $thisTreatmentTotalPrice = 0;
                $thisTreatmentInvoicedPrice = 0;
                $salestotal = 0;
                $thisTreatmentTotalCount = 0;

                $activeSheet->setCellValue('A' . $counter, $reportRow['name'])->getStyle('A' . $counter)->getFont()->setBold(true);

                $counter++;
                $counter++;

                $count = 0;
                $salestotal = 0;
                $servicetotal = 0;
                foreach ($reportRow['records'] as $thisDoctor) {
                    $thisDoctorServicePrice = 0;
                    $thisDoctorInvoicedPrice = 0;
                    $thisDoctorRecordsCount = 0;

                    $activeSheet->setCellValue('A' . $counter, $thisDoctor['name'])->getStyle('A' . $counter)->getFont()->setBold(true);

                    foreach ($thisDoctor['appointments'] as $thisAppointment) {
                        $counter++;

                        $grandServicePrice += (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                        $grandCount += 1;
                        $thisTreatmentTotalPrice += (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                        $thisTreatmentTotalCount += 1;
                        $serviceprice = (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                        $servicetotal += $serviceprice;
                        $thisDoctorServicePrice += $serviceprice;
                        $salestotal += $thisAppointment->Salestotal;

                        $grandTotalService += $thisAppointment->Salestotal;
                        $thisDoctorInvoicedPrice += $thisAppointment->Salestotal;
                        $thisTreatmentInvoicedPrice += $thisAppointment->Salestotal;
                        $thisDoctorRecordsCount += 1;

                        $count++;

                        $activeSheet->setCellValue('A' . $counter, $thisAppointment->patient_id)->getStyle('A' . $counter)->getFont();
                        $activeSheet->setCellValue('B' . $counter, $thisAppointment->patient->name)->getStyle('C' . $counter)->getFont();
                        $activeSheet->setCellValue('C' . $counter, \Carbon\Carbon::parse($thisAppointment->created_at)->format('M j, Y H:i A'))->getStyle('B' . $counter);
                        $activeSheet->setCellValue('D' . $counter, (array_key_exists($thisAppointment->doctor_id, $filters['doctors'])) ? $filters['doctors'][$thisAppointment->doctor_id]->name : '');
                        $activeSheet->setCellValue('E' . $counter, (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->name : '');
                        $activeSheet->setCellValue('F' . $counter, $thisAppointment->patient->email);
                        $activeSheet->setCellValue('G' . $counter, ($thisAppointment->scheduled_date) ? \Carbon\Carbon::parse($thisAppointment->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($thisAppointment->scheduled_time, null)->format('h:i A') : '-');
                        $activeSheet->setCellValue('H' . $counter, (array_key_exists($thisAppointment->city_id, $filters['cities'])) ? $filters['cities'][$thisAppointment->city_id]->name : '');
                        $activeSheet->setCellValue('I' . $counter, (array_key_exists($thisAppointment->location_id, $filters['locations'])) ? $filters['locations'][$thisAppointment->location_id]->name : '');
                        $activeSheet->setCellValue('J' . $counter, (array_key_exists($thisAppointment->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$thisAppointment->base_appointment_status_id]->name : '');
                        $activeSheet->setCellValue('K' . $counter, (array_key_exists($thisAppointment->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$thisAppointment->appointment_type_id]->name : '');

                        $activeSheet->setCellValue('L' . $counter, number_format($serviceprice, 2));
                        $activeSheet->setCellValue('M' . $counter, number_format($thisAppointment->Salestotal, 2));
                        $activeSheet->setCellValue('N' . $counter, (array_key_exists($thisAppointment->created_by, $filters['users'])) ? $filters['users'][$thisAppointment->created_by]->name : '');
                    }
                    $counter++;
                    $activeSheet->setCellValue('A' . $counter, $thisDoctor['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('B' . $counter, 'Appointments: ' . $thisDoctorRecordsCount)->getStyle('B' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('L' . $counter, number_format($thisDoctorServicePrice, 2))->getStyle('L' . $counter)->getFont()->setBold(true);
                    $activeSheet->setCellValue('M' . $counter, number_format($thisDoctorInvoicedPrice, 2))->getStyle('M' . $counter)->getFont()->setBold(true);
                    $counter++;
                    $counter++;
                }
                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                $activeSheet->setCellValue('A' . $counter, $reportRow['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, $thisTreatmentTotalCount)->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, 'Total: ' . $thisTreatmentTotalCount)->getStyle('C' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('L' . $counter, number_format($thisTreatmentTotalPrice, 2))->getStyle('L' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('M' . $counter, number_format($thisTreatmentInvoicedPrice, 2))->getStyle('M' . $counter)->getFont()->setBold(true);
                $counter++;
                $counter++;

            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('B' . $counter, 'Grand Total');
            $activeSheet->setCellValue('C' . $counter, $grandCount)->getStyle('B' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('L' . $counter, number_format($grandServicePrice, 2))->getStyle('L' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('M' . $counter, number_format($grandTotalService, 2))->getStyle('M' . $counter)->getFont()->setBold(true);
            $counter++;
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'Staff_Revenue_by_Service_Type' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /**
     * Load Account sales report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function accountsalesreportReport(Request $request)
    {

        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        $filters = array();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $reportData = \App\Reports\Invoices::getAccountSalesReport($request->all());

        $filters['reportData'] = $reportData;
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['patients'] = Patients::getAll(Auth::User()->account_id)->getDictionary();

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.staff_reports.staff_revenue_report.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'print':
                return view('admin.reports.staff_reports.staff_revenue_report.reportprint', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.staff_reports.staff_revenue_report.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Accounts Sales Report', 'landscape');
                break;
            case 'excel':
                self::accountsalesreportReportExcel($reportData, $filters, $start_date, $end_date);
                break;
            default:
                return view('admin.reports.staff_reports.staff_revenue_report.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
        }
    }

    /**
     * Daily Employee Stats (Summary) Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function accountsalesreportReportExcel($reportData, $filters, $start_date, $end_date)
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
        $activeSheet->setCellValue('B4', 'Service')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Payment Date')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Created By')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Patient')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Service Price')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Invoice Price')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Centre')->getStyle('H4')->getFont()->setBold(true);

        $counter = 5;
        $grandserviceprice = 0;
        $grandtotalservice = 0;

        foreach ($reportData as $reportRow) {

            $grandserviceprice += (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '';
            $grandtotalservice += $reportRow->total_price;

            $activeSheet->setCellValue('A' . $counter, $reportRow->id);
            $activeSheet->setCellValue('B' . $counter, (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '');
            $activeSheet->setCellValue('C' . $counter, ($reportRow->created_at) ? \Carbon\Carbon::parse($reportRow->created_at, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->created_at, null)->format('h:i A') : '-');
            $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '');
            $activeSheet->setCellValue('E' . $counter, (array_key_exists($reportRow->patient_id, $filters['patients'])) ? $filters['patients'][$reportRow->patient_id]->name : '');
            $activeSheet->setCellValue('F' . $counter, number_format((array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '', 2));
            $activeSheet->setCellValue('G' . $counter, number_format($reportRow->total_price, 2));
            $activeSheet->setCellValue('H' . $counter, (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '');

            $counter++;
        }

        $activeSheet->setCellValue('A' . $counter, '');
        $activeSheet->setCellValue('B' . $counter, '');
        $activeSheet->setCellValue('C' . $counter, '');
        $activeSheet->setCellValue('D' . $counter, '');
        $activeSheet->setCellValue('E' . $counter, '');
        $activeSheet->setCellValue('F' . $counter, '');
        $activeSheet->setCellValue('G' . $counter, '');
        $activeSheet->setCellValue('H' . $counter, '');
        $counter++;

        $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
        $activeSheet->setCellValue('F' . $counter, number_format($grandserviceprice, 2))->getStyle('A' . $counter)->getFont()->setBold(true);
        $activeSheet->setCellValue('G' . $counter, number_format($grandtotalservice, 2))->getStyle('A' . $counter)->getFont()->setBold(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'AccountSalesReport' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * Daily Employee Stats (Summary)
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private static function dailyEmployeeStatsSummary(Request $request)
    {
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        $filters = array();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionaryWithoutAll(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();

        $reportData = \App\Reports\Invoices::getDailyEmployeeStatsSummary($request->all(), $filters);
        $filters['reportData'] = $reportData;
        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.dailyemployeestatssummary.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'print':
                return view('admin.reports.dailyemployeestatssummary.reportprint', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.dailyemployeestatssummary.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Daily Employee Stats Summary', 'landscape');
                break;
            case 'excel':
                self::dailyEmployeeStatsSummaryExcel($reportData, $start_date, $end_date);
                break;
            default:
                return view('admin.reports.dailyemployeestatssummary.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
        }
    }

    /**
     * Daily Employee Stats (Summary) Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function dailyEmployeeStatsSummaryExcel($reportData, $start_date, $end_date)
    {
        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xls($spreadsheet);  /*----- Excel (Xls) Object*/

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

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . 'DailyEmployeeStatsSummary' . '.xls"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /**
     * Daily Employee Stats
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function dailyEmployeeStats(Request $request)
    {
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        $filters = array();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionaryWithoutAll(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();

        $reportData = \App\Reports\Invoices::getDailyEmployeeStats($request->all(), $filters);
        foreach ($reportData as $rowpackage)
            $filters['reportData'] = $reportData;
        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.dailyemployeestats.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'print':
                return view('admin.reports.dailyemployeestats.reportprint', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.dailyemployeestats.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Daily Employee Stats', 'landscape');
                break;
            case 'excel':
                self::dailyEmployeeStatsExcel($reportData, $start_date, $end_date);
                break;
            default:
                return view('admin.reports.dailyemployeestats.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
        }
    }

    /**
     * Daily Employee Stats Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function dailyEmployeeStatsExcel($reportData, $start_date, $end_date)
    {
        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xls($spreadsheet);  /*----- Excel (Xls) Object*/

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);

        $activeSheet->setCellValue('A2', 'Date')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        $activeSheet->setCellValue('A3', '');
        $activeSheet->setCellValue('B3', '');
        $activeSheet->setCellValue('C3', '');

        $activeSheet->setCellValue('A4', 'Doctor')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Service')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Total')->getStyle('C4')->getFont()->setBold(true);

        $counter = 5;
        $total = 0;
        if (count($reportData)) {
            $serviceGrandTotal = 0;
            foreach ($reportData as $reportPackageData) {
                $activeSheet->setCellValue('A' . $counter, $reportPackageData['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $counter++;
                $count = 0;
                $servicetotal = 0;
                foreach ($reportPackageData['records'] as $reportRow) {
                    $servicetotal += $reportRow['amount'];

                    $activeSheet->setCellValue('B' . $counter, $reportRow['name']);
                    $activeSheet->setCellValue('C' . $counter, number_format($reportRow['amount'], 2));
                    $counter++;
                }
                $serviceGrandTotal += $servicetotal;
                $activeSheet->setCellValue('A' . $counter, $reportPackageData['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, number_format($servicetotal, 2))->getStyle('A' . $counter)->getFont()->setBold(true);
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $activeSheet->setCellValue('B' . $counter, '');
            $activeSheet->setCellValue('C' . $counter, '');
            $counter++;
            $activeSheet->setCellValue('A' . $counter, '');
            $activeSheet->setCellValue('B' . $counter, 'Grand Total')->getStyle('B' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('C' . $counter, number_format($serviceGrandTotal, 2))->getStyle('C' . $counter)->getFont()->setBold(true);
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . 'DailyEmployeeStats' . '.xls"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /**
     * Load Sales By Services category.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function salesbyservicecategory(Request $request)
    {
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }
        $filters = array();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionaryWithoutAll(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['servicesheads'] = Services::where([['active', '=', '1'], ['parent_id', '=', '0'], ['slug', '!=', 'all']])->orderBy('name', 'asc')->get()->getDictionary();
        $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();

        $reportData = \App\Reports\Invoices::getSalesbyServiceCategory($request->all(), $filters);
        foreach ($reportData as $rowpackage)
            $filters['reportData'] = $reportData;
        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.salesbyservicescategory.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'print':
                return view('admin.reports.salesbyservicescategory.reportprint', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.salesbyservicescategory.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Daily Employee Stats', 'landscape');
                break;
            case 'excel':
                self::salesByServiceCategoryExcel($reportData, $start_date, $end_date);
                break;
            default:
                return view('admin.reports.salesbyservicescategory.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
        }
    }

    /**
     * Sales By Service Category
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function salesByServiceCategoryExcel($reportData, $start_date, $end_date)
    {
        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xls($spreadsheet);  /*----- Excel (Xls) Object*/

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);

        $activeSheet->setCellValue('A2', 'Date')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        $activeSheet->setCellValue('A3', '');
        $activeSheet->setCellValue('B3', '');
        $activeSheet->setCellValue('C3', '');
        $activeSheet->setCellValue('D3', '');

        $activeSheet->setCellValue('A4', 'Service Category')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Service')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Quantity')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Price')->getStyle('D4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A5', '');
        $activeSheet->setCellValue('B5', '');
        $activeSheet->setCellValue('C5', '');
        $activeSheet->setCellValue('D5', '');

        $counter = 6;
        $total = 0;
        if (count($reportData)) {
            $grandqty = 0;
            $serviceGrandTotal = 0;
            foreach ($reportData as $reportPackageData) {
                $activeSheet->setCellValue('A' . $counter, $reportPackageData['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $counter++;
                $qty = 0;
                $serviceheadtotal = 0;
                foreach ($reportPackageData['records'] as $reportRow) {
                    $qty += $reportRow['qty'];
                    $serviceheadtotal += $reportRow['amount'];

                    $activeSheet->setCellValue('B' . $counter, $reportRow['name']);
                    $activeSheet->setCellValue('C' . $counter, number_format($reportRow['qty']));
                    $activeSheet->setCellValue('D' . $counter, number_format($reportRow['amount'], 2));
                    $counter++;
                }
                $grandqty += $qty;
                $serviceGrandTotal += $serviceheadtotal;

                $activeSheet->setCellValue('A' . $counter, $reportPackageData['name'])->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, 'Total')->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, number_format($qty))->getStyle('C' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('D' . $counter, number_format($serviceheadtotal, 2))->getStyle('D' . $counter)->getFont()->setBold(true);
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $activeSheet->setCellValue('B' . $counter, '');
            $activeSheet->setCellValue('C' . $counter, '');
            $activeSheet->setCellValue('D' . $counter, '');

            $counter++;
            $activeSheet->setCellValue('A' . $counter, '');
            $activeSheet->setCellValue('B' . $counter, 'Grand Total')->getStyle('B' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('C' . $counter, number_format($grandqty))->getStyle('C' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('D' . $counter, number_format($serviceGrandTotal, 2))->getStyle('D' . $counter)->getFont()->setBold(true);

        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . 'Sales By Service Category' . '.xls"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /**
     * Load Discount Report.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function discountReport(Request $request)
    {
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        $filters = array();

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $reportData = \App\Reports\Invoices::getdiscountReport($request->all());

        $filters['reportData'] = $reportData;
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionaryWithoutAll(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['patients'] = Patients::getAll(Auth::User()->account_id)->getDictionary();
        $filters['discounts'] = Discounts::getDiscount(session('account_id'))->getDictionary();

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.discountreport.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'print':
                return view('admin.reports.discountreport.reportprint', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.discountreport.reportpdf', compact('reportData', 'filters', 'start_date', 'end_date'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('Accounts Sales Report', 'landscape');
                break;
            case 'excel':
                self::discountreportexcel($reportData, $filters, $start_date, $end_date);
                break;
            default:
                return view('admin.reports.discountreport.report', compact('reportData', 'filters', 'start_date', 'end_date'));
                break;
        }
    }

    /**
     * Discount Report
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function discountreportexcel($reportData, $filters, $start_date, $end_date)
    {

        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xls($spreadsheet);  /*----- Excel (Xls) Object*/

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);

        $activeSheet->setCellValue('A2', 'Date')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        $activeSheet->setCellValue('A3', '');
        $activeSheet->setCellValue('B3', '');

        $activeSheet->setCellValue('A4', 'Invoice No.')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'Service')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Payment Date')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Created By')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Patient')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Service Price')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Discount Name')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Discount Type')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Discount Amount')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Invoice Price')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Centre')->getStyle('K4')->getFont()->setBold(true);

        $counter = 5;
        $grandserviceprice = 0;
        $grandtotalservice = 0;

        foreach ($reportData as $reportRow) {

            $grandserviceprice += (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '';
            $grandtotalservice += $reportRow->total_price;

            $activeSheet->setCellValue('A' . $counter, $reportRow->id);
            $activeSheet->setCellValue('B' . $counter, (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '');
            $activeSheet->setCellValue('C' . $counter, ($reportRow->created_at) ? \Carbon\Carbon::parse($reportRow->created_at, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->created_at, null)->format('h:i A') : '-');
            $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '');
            $activeSheet->setCellValue('E' . $counter, (array_key_exists($reportRow->patient_id, $filters['patients'])) ? $filters['patients'][$reportRow->patient_id]->name : '');
            $activeSheet->setCellValue('F' . $counter, number_format((array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '', 2));
            $activeSheet->setCellValue('G' . $counter, (array_key_exists($reportRow->discount_id, $filters['discounts'])) ? $filters['discounts'][$reportRow->discount_id]->name : '');
            $activeSheet->setCellValue('H' . $counter, $reportRow->discount_type);
            $activeSheet->setCellValue('I' . $counter, $reportRow->discount_price);
            $activeSheet->setCellValue('J' . $counter, number_format($reportRow->total_price, 2));
            $activeSheet->setCellValue('K' . $counter, (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '');

            $counter++;
        }
        $activeSheet->setCellValue('A' . $counter, '');
        $counter++;

        $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
        $activeSheet->setCellValue('F' . $counter, number_format($grandserviceprice, 2))->getStyle('F' . $counter)->getFont()->setBold(true);
        $activeSheet->setCellValue('J' . $counter, number_format($grandtotalservice, 2))->getStyle('J' . $counter)->getFont()->setBold(true);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . 'Discount Report.xls"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }
}
