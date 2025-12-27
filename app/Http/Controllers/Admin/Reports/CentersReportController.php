<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Helpers\dateType;
use App\Models\Appointments;
use App\Models\Invoices;
use App\Models\InvoiceStatuses;
use App\Models\Leads;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\Cities;
use App\Models\Regions;
use App\Models\LeadStatuses;
use App\Helpers\ACL;
use App\User;
use App\Reports\Treatments;
use Auth;
use App\Models\Doctors;
use App\Models\Locations;
use App\Models\Services;
use App\Models\AppointmentStatuses;
use App\Models\AppointmentTypes;
use Barryvdh\DomPDF\Facade as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Carbon\Carbon;
use Config;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App;

class CentersReportController extends Controller
{
    /*
     * Index for centers
     */
    public function center()
    {
        if (!Gate::allows('centers_reports_manage')) {
            return abort(401);
        }
        $cities = Cities::getActiveSorted(ACL::getUserCities());
        $cities->prepend('Select City', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('All', '');

        return view('admin.reports.clientswithcompletedtreatments.index', compact('cities', 'regions', 'locations'));
    }

    /**
     * Load Report
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function reportLoad(Request $request)
    {
        switch ($request->get('report_type')) {
            case 'client_with_Completed_treatment':
                return self::clientwithcompletedtreatment($request);
                break;
            case 'client_with_not_Completed_treatment':
                return self::ClientWithNocompletedtreatment($request);
                break;
            case 'clients_took_treatments_particular_month':
                return self::clientwithtreatmentMonth($request);
                break;
            case 'clients_with_birthday_days':
                return self::clientswithbirthdaydays($request);
                break;
            default:
                return self::clientwithcompletedtreatment($request);
                break;
        }
    }

    /*
     * Load report for client who completed their treatment
     *
     */
    public function clientwithcompletedtreatment(Request $request)
    {
        if (!Gate::allows('centers_reports_client_with_Completed_treatment')) {
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

        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $leadAppointmentData = [];
            $message = $date_response['message'];
        } else {
            $reportData = Treatments::clientcompletedtreatment($request->all());

            $leadAppointmentData = array();
            $patients = array();

            foreach ($reportData as $leaddata) {
                if (!in_array($leaddata->patient_id, $patients)) {
                    $leadAppointmentData[$leaddata->patient_id] = array(
                        'name' => $leaddata->name,
                        'city_id' => $leaddata->city_id,
                        'region_id' => $leaddata->region_id,
                        'children' => array(),
                    );
                    $patients[] = $leaddata->patient_id;
                }
                $leadAppointmentData[$leaddata->patient_id]['children'][$leaddata->id] = $leaddata;
            }
            $message = null;
        }
        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['regions'] = Regions::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_statuses'] = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_types'] = AppointmentTypes::getAllRecords(Auth::User()->account_id)->getDictionary();

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.clientswithcompletedtreatments.report', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.clientswithcompletedtreatments.reportpdf', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Clients With Completed Treatments', 'landscape');
                break;
            case 'excel':
                self::ClientWithCompletedTreatmentExcel($leadAppointmentData, $filters, $start_date, $end_date, $message);
                break;
            case 'print':
                return view('admin.reports.clientswithcompletedtreatments.reportprint', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            default:
                return view('admin.reports.clientswithcompletedtreatments.report', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Client with completed treatment excel
     * @param (mixed) $reportData
     * @param (mixed) $filters
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function ClientWithCompletedTreatmentExcel($leadAppointmentData, $filters, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Client Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Created At')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Doctor')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Service')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Email')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Scheduled')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'City')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Center')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Status')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'type')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Created by')->getStyle('L4')->getFont()->setBold(true);

        $counter = 5;
        if (count($leadAppointmentData)) {
            $grandcount = 0;
            foreach ($leadAppointmentData as $reportlead) {
                $activeSheet->setCellValue('B' . $counter, $reportlead['name'])->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, (array_key_exists($reportlead['region_id'], $filters['regions'])) ? $filters['regions'][$reportlead['region_id']]->name : '')->getStyle('C' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportlead['city_id'], $filters['cities'])) ? $filters['cities'][$reportlead['city_id']]->name : '')->getStyle('D' . $counter)->getFont()->setBold(true);

                $counter++;
                $count = 0;
                foreach ($reportlead['children'] as $reportAppointments) {

                    $activeSheet->setCellValue('A' . $counter, $reportAppointments->patient->id);
                    $activeSheet->setCellValue('B' . $counter, $reportAppointments->patient->name);
                    $activeSheet->setCellValue('C' . $counter, \Carbon\Carbon::parse($reportAppointments->created_at)->format('M j, Y H:i A'));
                    $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportAppointments->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportAppointments->doctor_id]->name : '');
                    $activeSheet->setCellValue('E' . $counter, (array_key_exists($reportAppointments->service_id, $filters['services'])) ? $filters['services'][$reportAppointments->service_id]->name : '');
                    $activeSheet->setCellValue('F' . $counter, $reportAppointments->patient->email);
                    $activeSheet->setCellValue('G' . $counter, ($reportAppointments->scheduled_date) ? \Carbon\Carbon::parse($reportAppointments->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportAppointments->scheduled_time, null)->format('h:i A') : '-');
                    $activeSheet->setCellValue('H' . $counter, (array_key_exists($reportAppointments->city_id, $filters['cities'])) ? $filters['cities'][$reportAppointments->city_id]->name : '');
                    $activeSheet->setCellValue('I' . $counter, (array_key_exists($reportAppointments->location_id, $filters['locations'])) ? $filters['locations'][$reportAppointments->location_id]->name : '');
                    $activeSheet->setCellValue('J' . $counter, (array_key_exists($reportAppointments->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportAppointments->base_appointment_status_id]->name : '');
                    $activeSheet->setCellValue('K' . $counter, (array_key_exists($reportAppointments->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportAppointments->appointment_type_id]->name : '');
                    $activeSheet->setCellValue('L' . $counter, (array_key_exists($reportAppointments->created_by, $filters['users'])) ? $filters['users'][$reportAppointments->created_by]->name : '');
                    $counter++;
                    $grandcount++;
                    $count++;
                }
                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                $activeSheet->setCellValue('B' . $counter, $reportlead['name'])->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, 'Total');
                $activeSheet->setCellValue('D' . $counter, $count)->getStyle('D' . $counter)->getFont()->setBold(true);
                $counter++;

                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('B' . $counter, 'Grand Total');
            $activeSheet->setCellValue('C' . $counter, $grandcount)->getStyle('C' . $counter)->getFont()->setBold(true);
            $counter++;

        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'clientswithcompletedtreatments' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /*
     * Load report for client who completed their treatment
     *
     */
    public function ClientWithNocompletedtreatment(Request $request)
    {

        if (!Gate::allows('centers_reports_client_with_not_Completed_treatment')) {
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

        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $leadAppointmentData = [];
            $message = $date_response['message'];
        } else {
            $reportData = Treatments::ClientWithNocompletedtreatment($request->all());
            $leadAppointmentData = array();
            $patients = array();
            foreach ($reportData as $leaddata) {
                if (!in_array($leaddata->patient_id, $patients)) {
                    $leadAppointmentData[$leaddata->patient_id] = array(
                        'name' => $leaddata->name,
                        'city_id' => $leaddata->city_id,
                        'region_id' => $leaddata->region_id,
                        'children' => array(),
                    );
                    $patients[] = $leaddata->patient_id;
                }
                $leadAppointmentData[$leaddata->patient_id]['children'][$leaddata->id] = $leaddata;
            }
            $message = null;
        }

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');
        $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['regions'] = Regions::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_statuses'] = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_types'] = AppointmentTypes::getAllRecords(Auth::User()->account_id)->getDictionary();
        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.clientwithnotCompletedtreatments.report', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.clientwithnotCompletedtreatments.reportpdf', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Clients With Not Completed Treatments', 'landscape');
                break;
            case 'excel':
                self::ClientWithNotCompletedTreatmentExcel($leadAppointmentData, $filters, $start_date, $end_date, $message);
                break;
            case 'print':
                return view('admin.reports.clientwithnotCompletedtreatments.reportprint', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            default:
                return view('admin.reports.clientwithnotCompletedtreatments.report', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Client with completed treatment excel
     * @param (mixed) $reportData
     * @param (mixed) $filters
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function ClientWithNotCompletedTreatmentExcel($leadAppointmentData, $filters, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Client Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Created At')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Doctor')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Service')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Email')->getStyle('F3')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Scheduled')->getStyle('G3')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'City')->getStyle('H3')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Center')->getStyle('I3')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Status')->getStyle('J3')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'type')->getStyle('K3')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Created by')->getStyle('L3')->getFont()->setBold(true);

        $counter = 5;
        if (count($leadAppointmentData)) {
            $grandcount = 0;
            foreach ($leadAppointmentData as $reportlead) {
                $activeSheet->setCellValue('B' . $counter, $reportlead['name'])->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, (array_key_exists($reportlead['region_id'], $filters['regions'])) ? $filters['regions'][$reportlead['region_id']]->name : '')->getStyle('C' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportlead['city_id'], $filters['cities'])) ? $filters['cities'][$reportlead['city_id']]->name : '')->getStyle('D' . $counter)->getFont()->setBold(true);

                $counter++;
                $count = 0;
                foreach ($reportlead['children'] as $reportAppointments) {

                    $activeSheet->setCellValue('A' . $counter, $reportAppointments->patient->id);
                    $activeSheet->setCellValue('B' . $counter, $reportAppointments->patient->name);
                    $activeSheet->setCellValue('C' . $counter, \Carbon\Carbon::parse($reportAppointments->created_at)->format('M j, Y H:i A'));
                    $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportAppointments->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportAppointments->doctor_id]->name : '');
                    $activeSheet->setCellValue('E' . $counter, (array_key_exists($reportAppointments->service_id, $filters['services'])) ? $filters['services'][$reportAppointments->service_id]->name : '');
                    $activeSheet->setCellValue('F' . $counter, $reportAppointments->patient->email);
                    $activeSheet->setCellValue('G' . $counter, ($reportAppointments->scheduled_date) ? \Carbon\Carbon::parse($reportAppointments->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportAppointments->scheduled_time, null)->format('h:i A') : '-');
                    $activeSheet->setCellValue('H' . $counter, (array_key_exists($reportAppointments->city_id, $filters['cities'])) ? $filters['cities'][$reportAppointments->city_id]->name : '');
                    $activeSheet->setCellValue('I' . $counter, (array_key_exists($reportAppointments->location_id, $filters['locations'])) ? $filters['locations'][$reportAppointments->location_id]->name : '');
                    $activeSheet->setCellValue('J' . $counter, (array_key_exists($reportAppointments->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportAppointments->base_appointment_status_id]->name : '');
                    $activeSheet->setCellValue('K' . $counter, (array_key_exists($reportAppointments->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportAppointments->appointment_type_id]->name : '');
                    $activeSheet->setCellValue('L' . $counter, (array_key_exists($reportAppointments->created_by, $filters['users'])) ? $filters['users'][$reportAppointments->created_by]->name : '');
                    $counter++;
                    $grandcount++;
                    $count++;
                }
                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                $activeSheet->setCellValue('B' . $counter, $reportlead['name'])->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, 'Total');
                $activeSheet->setCellValue('D' . $counter, $count)->getStyle('D' . $counter)->getFont()->setBold(true);
                $counter++;

                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('B' . $counter, 'Grand Total');
            $activeSheet->setCellValue('C' . $counter, $grandcount)->getStyle('B' . $counter)->getFont()->setBold(true);
            $counter++;

        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'clientswithcompletedtreatments' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /*
    * Load report for client who completed their treatment
    *
    */
    public function clientwithtreatmentMonth(Request $request)
    {
        if (!Gate::allows('centers_reports_clients_took_treatments_particular_month')) {
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

        $date_response = dateType::dateTypeDecision($start_date, $end_date);

        if (!$date_response['status']) {
            $leadAppointmentData = [];
            $message = $date_response['message'];
        } else {
            $reportData = Treatments::clientwithtreatmentsinparticularmonth($request->all());
            $leadAppointmentData = array();
            $patients = array();
            foreach ($reportData as $leaddata) {
                if (!in_array($leaddata->patient_id, $patients)) {
                    $leadAppointmentData[$leaddata->patient_id] = array(
                        'name' => $leaddata->name,
                        'city_id' => $leaddata->city_id,
                        'region_id' => $leaddata->region_id,
                        'children' => array(),
                    );
                    $patients[] = $leaddata->patient_id;
                }
                $leadAppointmentData[$leaddata->patient_id]['children'][$leaddata->id] = $leaddata;
            }
            $message = null;
        }

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');
        $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['regions'] = Regions::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['users'] = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $filters['cities'] = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['doctors'] = Doctors::getAll(Auth::User()->account_id)->getDictionary();
        $filters['locations'] = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['services'] = Services::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_statuses'] = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);
        $filters['appointment_types'] = AppointmentTypes::getAllRecords(Auth::User()->account_id)->getDictionary();

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.clienttooktreatmentinparticularmonth.report', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            case 'pdf':

                $content = view('admin.reports.clienttooktreatmentinparticularmonth.reportpdf', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('Clients With Completed Treatments in Particular Month', 'landscape');
                break;
            case 'excel':
                self::ClientWithTreatmentMonthExcel($leadAppointmentData, $filters, $start_date, $end_date, $message);
                break;
            case 'print':
                return view('admin.reports.clienttooktreatmentinparticularmonth.reportprint', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
            default:
                return view('admin.reports.clienttooktreatmentinparticularmonth.report', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Client treatment  in paricular month excel
     * @param (mixed) $reportData
     * @param (mixed) $filters
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function ClientWithTreatmentMonthExcel($leadAppointmentData, $filters, $start_date, $end_date, $message)
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
        $activeSheet->setCellValue('B4', 'Client Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Created At')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'Doctor')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'Service')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Email')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Scheduled')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'City')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'Center')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Status')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'type')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Created by')->getStyle('L4')->getFont()->setBold(true);

        $counter = 5;
        if (count($leadAppointmentData)) {
            $grandcount = 0;
            foreach ($leadAppointmentData as $reportlead) {
                $activeSheet->setCellValue('B' . $counter, $reportlead['name'])->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, (array_key_exists($reportlead['region_id'], $filters['regions'])) ? $filters['regions'][$reportlead['region_id']]->name : '')->getStyle('C' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportlead['city_id'], $filters['cities'])) ? $filters['cities'][$reportlead['city_id']]->name : '')->getStyle('D' . $counter)->getFont()->setBold(true);

                $counter++;
                $count = 0;
                foreach ($reportlead['children'] as $reportAppointments) {

                    $activeSheet->setCellValue('A' . $counter, $reportAppointments->patient->id);
                    $activeSheet->setCellValue('B' . $counter, $reportAppointments->patient->name);
                    $activeSheet->setCellValue('C' . $counter, \Carbon\Carbon::parse($reportAppointments->created_at)->format('M j, Y H:i A'));
                    $activeSheet->setCellValue('D' . $counter, (array_key_exists($reportAppointments->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportAppointments->doctor_id]->name : '');
                    $activeSheet->setCellValue('E' . $counter, (array_key_exists($reportAppointments->service_id, $filters['services'])) ? $filters['services'][$reportAppointments->service_id]->name : '');
                    $activeSheet->setCellValue('F' . $counter, $reportAppointments->patient->email);
                    $activeSheet->setCellValue('G' . $counter, ($reportAppointments->scheduled_date) ? \Carbon\Carbon::parse($reportAppointments->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportAppointments->scheduled_time, null)->format('h:i A') : '-');
                    $activeSheet->setCellValue('H' . $counter, (array_key_exists($reportAppointments->city_id, $filters['cities'])) ? $filters['cities'][$reportAppointments->city_id]->name : '');
                    $activeSheet->setCellValue('I' . $counter, (array_key_exists($reportAppointments->location_id, $filters['locations'])) ? $filters['locations'][$reportAppointments->location_id]->name : '');
                    $activeSheet->setCellValue('J' . $counter, (array_key_exists($reportAppointments->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportAppointments->base_appointment_status_id]->name : '');
                    $activeSheet->setCellValue('K' . $counter, (array_key_exists($reportAppointments->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportAppointments->appointment_type_id]->name : '');
                    $activeSheet->setCellValue('L' . $counter, (array_key_exists($reportAppointments->created_by, $filters['users'])) ? $filters['users'][$reportAppointments->created_by]->name : '');
                    $counter++;
                    $grandcount++;
                    $count++;
                }
                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;

                $activeSheet->setCellValue('B' . $counter, $reportlead['name'])->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, 'Total');
                $activeSheet->setCellValue('D' . $counter, $count)->getStyle('D' . $counter)->getFont()->setBold(true);
                $counter++;

                $activeSheet->setCellValue('A' . $counter, '');
                $counter++;
            }
            $activeSheet->setCellValue('A' . $counter, '');
            $counter++;

            $activeSheet->setCellValue('B' . $counter, 'Grand Total');
            $activeSheet->setCellValue('C' . $counter, $grandcount)->getStyle('B' . $counter)->getFont()->setBold(true);
            $counter++;

        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'clienttooktreatmentinparticularmonth' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    /*
    * Load report for client who completed their treatment
    *
    */
    public function clientswithbirthdaydays(Request $request)
    {
        if (!Gate::allows('centers_reports_clients_with_birthday_days')) {
            return abort(401);
        }
        if (isset($request['date_range']) && $request['date_range']) {
            $date_range = explode(' - ', $request['date_range']);
            $start_date = date('m-d', strtotime($date_range[0]));
            $end_date = date('m-d', strtotime($date_range[1]));
            $start = date('Y-m-d', strtotime($date_range[0]));
            $end = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }
        $filters = array();

        $date_response = dateType::dateTypeDecision($start, $end);

        if (!$date_response['status']) {
            $leads = [];
            $message = $date_response['message'];
        } else {
            $leads = Treatments::clientswithbirthday($request->all());
            foreach ($leads as $key => $leaddata) {
                $dob = date('m-d', strtotime($leaddata->dob));
                if ($dob >= $start_date && $dob <= $end_date) {
                } else {
                    unset($leads[$key]);
                }
            }
            $message = null;
        }

        $Cities = Cities::get()->getDictionary();
        $lead_status = LeadStatuses::get()->getDictionary();
        $services = Services::get()->getDictionary();
        $todaydate = Carbon::now()->toDateString();
        $users = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $region = Regions::get()->getDictionary();

        $filters = array();
        $filters['Cities'] = $Cities;
        $filters['lead_status'] = $lead_status;
        $filters['services'] = $services;
        $filters['todaydate'] = $todaydate;
        $filters['users'] = $users;

        switch ($request->get('medium_type')) {
            case 'web':
                return view('admin.reports.clientwithbirhtday.report', compact('leads', 'start_date', 'end_date', 'Cities', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'));
                break;
            case 'pdf':
                $content = view('admin.reports.clientwithbirhtday.reportpdf', compact('leads', 'start_date', 'end_date', 'Cities', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($content);
                $pdf->setPaper('A3', 'landscape');
                return $pdf->stream('clientswithcompletedtreatments', 'landscape');
                break;
            case 'excel':
                self::ClientWithBirthdayExcel($leads, $start_date, $end_date, $Cities, $lead_status, $services, $todaydate, $users, $region, $message);
                break;
            case 'print':
                return view('admin.reports.clientwithbirhtday.reportprint', compact('leads', 'start_date', 'end_date', 'Cities', 'lead_status', 'services', 'todaydate', 'users', 'region', 'message'));
                break;
            default:
                return view('admin.reports.clientwithbirhtday.report', compact('leadAppointmentData', 'filters', 'start_date', 'end_date', 'message'));
                break;
        }
    }

    /**
     * Client with Birthday x days Excel
     * @param (mixed) $reportData
     * @param (mixed) $start_date
     * @param (mixed) $end_date
     *
     * @return \Illuminate\Http\Response
     */
    private static function ClientWithBirthdayExcel($leads, $start_date, $end_date, $Cities, $lead_status, $services, $todaydate, $users, $regions, $message)
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

        $activeSheet->setCellValue('A4', '#')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B4', 'ID')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C4', 'Full Name')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D4', 'CNIC')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E4', 'DOB')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F4', 'Email')->getStyle('F4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G4', 'Gender')->getStyle('G4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H4', 'Region')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('I4', 'City')->getStyle('I4')->getFont()->setBold(true);
        $activeSheet->setCellValue('J4', 'Lead Status')->getStyle('J4')->getFont()->setBold(true);
        $activeSheet->setCellValue('K4', 'Services')->getStyle('K4')->getFont()->setBold(true);
        $activeSheet->setCellValue('L4', 'Created By')->getStyle('L4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A5', '');

        $counter = 6;
        $total = 0;
        if (count($leads)) {
            $count = 1;
            foreach ($leads as $leads) {
                if ($leads->gender == '1') {
                    $gender = 'Male';
                } else {
                    $gender = 'Female';
                }
                $activeSheet->setCellValue('A' . $counter, $count++);
                $activeSheet->setCellValue('B' . $counter, $leads->patient_id);
                $activeSheet->setCellValue('C' . $counter, $leads->name);
                $activeSheet->setCellValue('D' . $counter, $leads->cnic);
                $activeSheet->setCellValue('E' . $counter, $leads->dob);
                $activeSheet->setCellValue('F' . $counter, $leads->email);
                $activeSheet->setCellValue('G' . $counter, $gender);
                $activeSheet->setCellValue('H' . $counter, $regions[$leads->region_id]->name);
                $activeSheet->setCellValue('I' . $counter, $Cities[$leads->city_id]->name);
                $activeSheet->setCellValue('J' . $counter, $lead_status[$leads->lead_status_id]->name);
                $activeSheet->setCellValue('K' . $counter, $services[$leads->service_id]->name);
                $activeSheet->setCellValue('L' . $counter, $users[$leads->created_by]->name);
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
        header('Content-Disposition: attachment;filename="' . 'ClientWithBirthdayXDaysExcel' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }
}
