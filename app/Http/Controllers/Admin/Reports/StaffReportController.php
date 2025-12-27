<?php

namespace App\Http\Controllers\Admin\Reports;

//use App\Exports\PatientExport;
use App\Models\Locations;
use App\Reports\Staff;
use Illuminate\Http\Request;
use App\Models\Regions;

//use App\Models\Telecomprovider;
//use App\Models\Telecomprovidernumber;
use App\User;
use App\Models\Cities;
use App\Models\Services;
use App\Helpers\NodesTree;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use App\Helpers\ACL;
use Auth;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;
use Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Helpers\Widgets\TelecomproviderWidget;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Helpers\dateType;

class StaffReportController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function report()
    {
        if (!Gate::allows('staff_listing_reports_manage')) {
            return abort(401);
        }

        $cities = Cities::getActiveSorted(ACL::getUserCities());
        $cities->prepend('Select City', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        $usersList = [1, 2, 5]; // 'administrator_id' => 1,'application_user_id' => 2,'practitioner_id' => 5,'asthatic_operator_id' => 5,

        $employees = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $operators = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array('' => 'Select Staff');

        $staff = ($select_All + $employees->toArray() + $operators->toArray());

        $application_user = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $application_user->prepend('Select Application User', '');

        $practionars = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $practionars->prepend('Select Doctor', '');

        $usersListData = [
            '' => 'Select Staff Type',
            2 => 'Application User',
            5 => 'Practitioner',
        ];

        $users = User::getUsersleadReport();
        $users->prepend('Created by', '');

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('Select Centre', '');

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);

        $Services = $parentGroups->nodeList;

        $telcomprovider = TelecomproviderWidget::telecomprovider();

        return view('admin.reports.staff_reports.index', compact('Services', 'cities', 'regions', 'users', 'staff', 'application_user', 'practionars', 'usersListData', 'locations', 'telcomprovider'));

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function reportLoad(Request $request)
    {
        switch ($request->get('report_type')) {
            case 'region_wise_staff_list':
                return self::regionWiseStaffReport($request);
                break;
            case 'centre_wise_staff_list':
                return self::centreWiseStaffReport($request);
                break;
            default:
                return self::regionWiseStaffReport($request);
                break;
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function regionWiseStaffReport(Request $request)
    {
        if (!Gate::allows('staff_listing_reports_region_wise_staff_list')) {
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
            $staffData = [];
            $filters = array();
            $regionNames = '';
            $totalRecords = '';
            $message = $date_response['message'];
        } else {
            $staff = Staff::staffReports($request->all());

            $staffData = [];

            $count = 0;

            foreach ($staff as $thisStaff) {
                if (count($thisStaff->doctorhaslocation) > 0 || count($thisStaff->user_has_locations) > 0) {
                    $count++;
                    $centreName = '';
                    $city = '';
                    $region = '';
                    if ($thisStaff->gender == '1') {
                        $gender = 'Male';
                    } else {
                        $gender = 'Female';
                    }
                    if (count($thisStaff->doctorhaslocation) > 0) {
                        foreach ($thisStaff->doctorhaslocation as $thisLocation) {
                            if (in_array($thisLocation->location->region->id, ACL::getUserRegions()) || in_array($thisLocation->location->id, ACL::getUserRegions())) {
                                if ($thisLocation->location->slug == 'custom') {
                                    $count++;
                                    $centreName = $thisLocation->location->name;
                                    $city = $thisLocation->location->city->name;
                                    $region = $thisLocation->location->region->name;
                                    $staffData[$region][$count] = [
                                        'id' => $thisStaff->id,
                                        'name' => $thisStaff->name,
                                        'email' => $thisStaff->email,
                                        'gender' => $gender,
                                        'centre_name' => $centreName,
                                        'city' => $city,
                                        'region' => $region,
                                        'phone' => $thisStaff->phone,
                                    ];
                                }
                            }
                        }
                    }
                    if (count($thisStaff->user_has_locations) > 0) {
                        foreach ($thisStaff->user_has_locations as $thisLocation) {
                            if (in_array($thisLocation->location->region->id, ACL::getUserRegions()) || in_array($thisLocation->location->id, ACL::getUserCentres())) {
                                if ($thisLocation->location->slug == 'custom') {
                                    $count++;
                                    $centreName = $thisLocation->location->name;
                                    $city = $thisLocation->location->city->name;
                                    $region = $thisLocation->location->region->name;
                                    $staffData[$region][$count] = [
                                        'id' => $thisStaff->id,
                                        'name' => $thisStaff->name,
                                        'email' => $thisStaff->email,
                                        'gender' => $gender,
                                        'centre_name' => $centreName,
                                        'city' => $city,
                                        'region' => $region,
                                        'phone' => $thisStaff->phone,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            $regionNames = array_keys($staffData);
            $totalRecords = 0;
            foreach ($staffData as $data) {
                $totalRecords = $totalRecords + count($data);
            }
            $message = null;
        }

        $Cities = Cities::get()->getDictionary();
        $services = Services::get()->getDictionary();
        $todaydate = Carbon::now()->toDateString();
        $users = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $region = Regions::get()->getDictionary();
        $locations = Locations::get()->getDictionary();

        $filters = array();
        $filters['Cities'] = $Cities;
        $filters['services'] = $services;
        $filters['todaydate'] = $todaydate;
        $filters['users'] = $users;
        $filters['region'] = $region;
        $filters['locations'] = $locations;
        $filters['city'] = $Cities;
        $reportName = 'Region Wise Staff Report';

        switch ($request->get('medium_type')) {
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.staff_reports.report_region_wise_pdf', compact('staffData', 'regionNames', 'reportName', 'totalRecords', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters', 'message'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('staffReport', 'landscape');
                break;
            case 'excel':
                self::reportExcelRegionWise($staffData, $regionNames, $reportName, $totalRecords, $start_date, $end_date, $Cities, $services, $todaydate, $users, $region, $locations, $filters, $message);
                break;
            case 'print':
                return view('admin.reports.staff_reports.report_region_wise_print', compact('staffData', 'regionNames', 'reportName', 'totalRecords', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters', 'message'));
                break;
            default:
                return view('admin.reports.staff_reports.report_region_wise', compact('staffData', 'regionNames', 'reportName', 'totalRecords', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters', 'message'));
                break;
        }
    }

    /**
     * @param $staff
     * @param $start_date
     * @param $end_date
     * @param $Cities
     * @param $services
     * @param $todaydate
     * @param $users
     * @param $region
     * @param $locations
     * @param $filters
     */
    private static function reportExcelRegionWise($staffData, $regionNames, $reportName, $totalRecords, $start_date, $end_date, $Cities, $services, $todaydate, $users, $region, $locations, $filters, $message)
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

        $activeSheet->setCellValue('A3', 'Report Name')->getStyle('A3')->getFont()->setBold(true);
        $activeSheet->setCellValue('B3', $reportName);

        $activeSheet->setCellValue('A4', '');

        $activeSheet->setCellValue('A5', 'Full Name')->getStyle('A5')->getFont()->setBold(true);
        $activeSheet->setCellValue('B5', 'Email')->getStyle('B5')->getFont()->setBold(true);
        $activeSheet->setCellValue('C5', 'Gender')->getStyle('C5')->getFont()->setBold(true);
        $activeSheet->setCellValue('D5', 'Centre')->getStyle('D5')->getFont()->setBold(true);
        $activeSheet->setCellValue('E5', 'City')->getStyle('E5')->getFont()->setBold(true);
        $activeSheet->setCellValue('F5', 'Region')->getStyle('F5')->getFont()->setBold(true);
        $activeSheet->setCellValue('G5', 'Phone')->getStyle('G5')->getFont()->setBold(true);

        $activeSheet->setCellValue('A6', '');

        $counter = 6;
        $total = 0;
        if (count($staffData)) {
            $count = -1;
            foreach ($staffData as $thisStaff) {
                $count++;
                $activeSheet->setCellValue('A' . $counter, $regionNames[$count])->getStyle('A' . $counter)->getFont()->setBold(true);
                $thisRegionName = $regionNames[$count];
                $thisCounter = $counter;
                //$activeSheet->setCellValue('B' . $counter, 'Total');
                //$activeSheet->setCellValue('C' . $counter, count($thisStaff));

                $counter++ ;

                foreach ($thisStaff as $user) {
                    $counter++;

                    $activeSheet->setCellValue('A' . $counter, $user['name']);
                    $activeSheet->setCellValue('B' . $counter, $user['email']);
                    $activeSheet->setCellValue('C' . $counter, $user['gender']);
                    $activeSheet->setCellValue('D' . $counter, $user['centre_name']);
                    $activeSheet->setCellValue('E' . $counter, $user['city']);
                    $activeSheet->setCellValue('F' . $counter, $user['region']);
                    $activeSheet->setCellValue('G' . $counter, $user['phone']);
                }
                $counter++;
                $activeSheet->setCellValue('A' . $counter, $thisRegionName)->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, 'Total')->getStyle('B' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, count($thisStaff));
                $counter+=2;
            }
            $counter++;
            $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('B' . $counter, $totalRecords)->getStyle('B' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. 'Staff Report Region Wise'.'.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function centreWiseStaffReport(Request $request)
    {
        if (!Gate::allows('staff_listing_reports_centre_wise_staff_list')) {
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
            $staffData = [];
            $filters = array();
            $regionNames = '';
            $totalRecords = '';
            $message = $date_response['message'];
        } else {
            $staff = Staff::staffReports($request->all());
            $staffData = [];
            $count = 0;
            foreach ($staff as $thisStaff) {
                if (count($thisStaff->doctorhaslocation) > 0 || count($thisStaff->user_has_locations) > 0) {
                    $count++;
                    $centreName = '';
                    $city = '';
                    $region = '';
                    if ($thisStaff->gender == '1') {
                        $gender = 'Male';
                    } else {
                        $gender = 'Female';
                    }
                    if (count($thisStaff->doctorhaslocation) > 0) {
                        foreach ($thisStaff->doctorhaslocation as $thisLocation) {
                            if (in_array($thisLocation->location->id, ACL::getUserCentres())) {
                                $count++;
                                $centreName = $thisLocation->location->name;
                                $city = $thisLocation->location->city->name;
                                $region = $thisLocation->location->region->name;
                                $staffData[$centreName][$count] = [
                                    'id' => $thisStaff->id,
                                    'name' => $thisStaff->name,
                                    'email' => $thisStaff->email,
                                    'gender' => $gender,
                                    'centre_name' => $centreName,
                                    'city' => $city,
                                    'region' => $region,
                                    'phone' => $thisStaff->phone,
                                ];
                            }
                        }
                    }
                    if (count($thisStaff->user_has_locations) > 0) {
                        foreach ($thisStaff->user_has_locations as $thisLocation) {
                            if (in_array($thisLocation->location->id, ACL::getUserCentres())) {
                                $count++;
                                $centreName = $thisLocation->location->name;
                                $city = $thisLocation->location->city->name;
                                $region = $thisLocation->location->region->name;
                                $staffData[$centreName][$count] = [
                                    'id' => $thisStaff->id,
                                    'name' => $thisStaff->name,
                                    'email' => $thisStaff->email,
                                    'gender' => $gender,
                                    'centre_name' => $centreName,
                                    'city' => $city,
                                    'region' => $region,
                                    'phone' => $thisStaff->phone,
                                ];
                            }
                        }
                    }
                }
            }
            $regionNames = array_keys($staffData);
            $totalRecords = 0;
            foreach ($staffData as $data) {
                $totalRecords = $totalRecords + count($data);
            }

            $message = null;
        }

        $Cities = Cities::get()->getDictionary();
        $services = Services::get()->getDictionary();
        $todaydate = Carbon::now()->toDateString();
        $users = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $region = Regions::get()->getDictionary();
        $locations = Locations::get()->getDictionary();

        $filters = array();
        $filters['Cities'] = $Cities;
        $filters['services'] = $services;
        $filters['todaydate'] = $todaydate;
        $filters['users'] = $users;
        $filters['region'] = $region;
        $filters['locations'] = $locations;
        $filters['city'] = $Cities;
        $reportName = 'Centre Wise Staff Report';

        switch ($request->get('medium_type')) {
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.staff_reports.report_region_wise_pdf', compact('staffData', 'regionNames', 'reportName', 'totalRecords', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters', 'message'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('staffReport', 'landscape');
                break;
            case 'excel':
                self::reportExcelCentreWise($staffData, $regionNames, $reportName, $totalRecords, $start_date, $end_date, $Cities, $services, $todaydate, $users, $region, $locations, $filters, $message);
                break;
            case 'print':
                return view('admin.reports.staff_reports.report_region_wise_print', compact('staffData', 'regionNames', 'reportName', 'totalRecords', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters', 'message'));
                break;
            default:
                return view('admin.reports.staff_reports.report_region_wise', compact('staffData', 'regionNames', 'reportName', 'totalRecords', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters', 'message'));
                break;
        }
    }

    /**
     * @param $staff
     * @param $start_date
     * @param $end_date
     * @param $Cities
     * @param $services
     * @param $todaydate
     * @param $users
     * @param $region
     * @param $locations
     * @param $filters
     */
    private static function reportExcelCentreWise($staffData, $regionNames, $reportName, $totalRecords, $start_date, $end_date, $Cities, $services, $todaydate, $users, $region, $locations, $filters, $message)
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

        $activeSheet->setCellValue('A3', 'Report Name')->getStyle('A3')->getFont()->setBold(true);
        $activeSheet->setCellValue('B3', $reportName);

        $activeSheet->setCellValue('A4', '');

        $activeSheet->setCellValue('A5', 'Full Name')->getStyle('A5')->getFont()->setBold(true);
        $activeSheet->setCellValue('B5', 'Email')->getStyle('B5')->getFont()->setBold(true);
        $activeSheet->setCellValue('C5', 'Gender')->getStyle('C5')->getFont()->setBold(true);
        $activeSheet->setCellValue('D5', 'Centre')->getStyle('D5')->getFont()->setBold(true);
        $activeSheet->setCellValue('E5', 'City')->getStyle('E5')->getFont()->setBold(true);
        $activeSheet->setCellValue('F5', 'Region')->getStyle('F5')->getFont()->setBold(true);
        $activeSheet->setCellValue('G5', 'Phone')->getStyle('G5')->getFont()->setBold(true);

        $activeSheet->setCellValue('A6', '');

        $counter = 6;
        $total = 0;
        if (count($staffData)) {
            $count = -1;
            foreach ($staffData as $thisStaff) {
                $count++;
                $activeSheet->setCellValue('A' . $counter, $regionNames[$count])->getStyle('A' . $counter)->getFont()->setBold(true);
                $thisRegionName = $regionNames[$count];
                $thisCounter = $counter;
                //$activeSheet->setCellValue('B' . $counter, 'Total');
                //$activeSheet->setCellValue('C' . $counter, count($thisStaff));

                $counter++ ;

                foreach ($thisStaff as $user) {
                    $counter++;

                    $activeSheet->setCellValue('A' . $counter, $user['name']);
                    $activeSheet->setCellValue('B' . $counter, $user['email']);
                    $activeSheet->setCellValue('C' . $counter, $user['gender']);
                    $activeSheet->setCellValue('D' . $counter, $user['centre_name']);
                    $activeSheet->setCellValue('E' . $counter, $user['city']);
                    $activeSheet->setCellValue('F' . $counter, $user['region']);
                    $activeSheet->setCellValue('G' . $counter, $user['phone']);
                }
                $counter++;
                $activeSheet->setCellValue('A' . $counter, $thisRegionName)->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('B' . $counter, 'Total')->getStyle('A' . $counter)->getFont()->setBold(true);
                $activeSheet->setCellValue('C' . $counter, count($thisStaff));
                $counter+=2;
            }
            $counter++;
            $activeSheet->setCellValue('A' . $counter, 'Grand Total')->getStyle('A' . $counter)->getFont()->setBold(true);
            $activeSheet->setCellValue('B' . $counter, $totalRecords)->getStyle('B' . $counter)->getFont()->setBold(true);
        } else {
            if ($message) {
                $activeSheet->setCellValue('A' . $counter, $message)->getStyle('A1')->getFont()->setBold(true);
            } else {
                $activeSheet->setCellValue('A' . $counter, 'No record round')->getStyle('A1')->getFont()->setBold(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. 'Staff Report Centre Wise'.'.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function defaultStaffReport(Request $request)
    {
        if ($request->get('date_range')) {
            $date_range = explode(' - ', $request->get('date_range'));
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }
        //dd($request->all());
        $staff = Staff::staffReports($request->all());

        $Cities = Cities::get()->getDictionary();
        $services = Services::get()->getDictionary();
        $todaydate = Carbon::now()->toDateString();
//        $users = User::get()->getDictionary();
        $users = User::getAllRecords(Auth::User()->account_id)->getDictionary();
        $region = Regions::get()->getDictionary();
        //$locations = Locations::where([['active', '=', '1'],['account_id', '=', session('account_id')],['slug','=','custom']])->get()->pluck('full_address', 'id');
        $locations = Locations::get()->getDictionary();
        //$locationsData = Locations::where([['active', '=', '1'],['account_id', '=', session('account_id')],['slug','=','custom']])->get()->select('full_address', 'id','city_id','region_id');
        //$locations->prepend('All', '');

        $filters = array();
        //$filters['staff'] = $staff;
        $filters['Cities'] = $Cities;
        $filters['services'] = $services;
        $filters['todaydate'] = $todaydate;
        $filters['users'] = $users;
        $filters['region'] = $region;
        $filters['locations'] = $locations;
        $filters['city'] = $Cities;
        //dd($staff);
        switch ($request->get('medium_type')) {
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.staff_reports.report_pdf', compact('staff', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters'));
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('staffReport', 'landscape');
                break;
            case 'excel':
                self::reportExcelDefault($staff, $start_date, $end_date, $Cities, $services, $todaydate, $users, $region, $locations, $filters);
                break;
            case 'print':
                return view('admin.reports.staff_reports.report_print', compact('staff', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters'));
                break;
            default:
                return view('admin.reports.staff_reports.report', compact('staff', 'start_date', 'end_date', 'Cities', 'services', 'todaydate', 'users', 'region', 'locations', 'filters'));
                break;
        }
    }

    /**
     * @param $staff
     * @param $start_date
     * @param $end_date
     * @param $Cities
     * @param $services
     * @param $todaydate
     * @param $users
     * @param $region
     * @param $locations
     * @param $filters
     */
    private static function reportExcelDefault($staff, $start_date, $end_date, $Cities, $services, $todaydate, $users, $region, $locations, $filters)
    {
        //dd($staff);
        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $Excel_writer = new Xls($spreadsheet);  /*----- Excel (Xls) Object*/

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1', 'Duration')->getStyle('A1')->getFont()->setBold(true);
        $activeSheet->setCellValue('B1', 'From ' . $start_date . ' to ' . $end_date);

        $activeSheet->setCellValue('A2', 'Date')->getStyle('A2')->getFont()->setBold(true);
        $activeSheet->setCellValue('B2', Carbon::now()->format('Y-m-d'));

        $activeSheet->setCellValue('A3', 'Report Name')->getStyle('A3')->getFont()->setBold(true);
        $activeSheet->setCellValue('B3', 'Staff Report');

        $activeSheet->setCellValue('A4', '');

        $activeSheet->setCellValue('A5', '#')->getStyle('A4')->getFont()->setBold(true);
        $activeSheet->setCellValue('B5', 'Full Name')->getStyle('B4')->getFont()->setBold(true);
        $activeSheet->setCellValue('C5', 'Email')->getStyle('C4')->getFont()->setBold(true);
        $activeSheet->setCellValue('D5', 'Gender')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('E5', 'Centre')->getStyle('D4')->getFont()->setBold(true);
        $activeSheet->setCellValue('F5', 'City')->getStyle('H4')->getFont()->setBold(true);
        $activeSheet->setCellValue('G5', 'Region')->getStyle('E4')->getFont()->setBold(true);
        $activeSheet->setCellValue('H5', 'Phone')->getStyle('G4')->getFont()->setBold(true);

        $activeSheet->setCellValue('A6', '');

        $counter = 6;
        $total = 0;
        if (count($staff)) {
            $count = 1;
            foreach ($staff as $thisStaff) {
                if ($thisStaff->gender == '1') {
                    $gender = 'Male';
                } else {
                    $gender = 'Female';
                }
                if (count($thisStaff->doctorhaslocation) > 0) {
                    {
                        $centre = $filters['locations'][$thisStaff->doctorhaslocation[0]->location_id]->name;
                        $region = $thisStaff->doctorhaslocation[0]->location->region->name;
                        $city = $filters['locations'][$thisStaff->doctorhaslocation[0]->location_id]->city->name;
                    }
                    if (count($thisStaff->user_has_locations) > 0) {
                        $centre = $thisStaff->user_has_locations[0]->location_id;
                        $region = $thisStaff->user_has_locations[0]->location->region->name;
                        $city = $filters['locations'][$thisStaff->user_has_locations[0]->location_id]->city->name;
                    }
                }
                $activeSheet->setCellValue('A' . $counter, $count++);
                $activeSheet->setCellValue('B' . $counter, $thisStaff->name);
                $activeSheet->setCellValue('C' . $counter, $thisStaff->email);
                $activeSheet->setCellValue('D' . $counter, $gender);
                if (isset($centre)) {
                    $activeSheet->setCellValue('E' . $counter, $centre);
                } else {
                    $activeSheet->setCellValue('E' . $counter, '');
                }
                if (isset($city)) {
                    $activeSheet->setCellValue('F' . $counter, $city);
                } else {
                    $activeSheet->setCellValue('F' . $counter, '');
                }
                if (isset($region)) {
                    $activeSheet->setCellValue('G' . $counter, $region);
                } else {
                    $activeSheet->setCellValue('G' . $counter, '');
                }
                $activeSheet->setCellValue('H' . $counter, $thisStaff->phone);
                //$activeSheet->setCellValue('H' . $counter, $Cities[$thisStaff->city_id]->name);
                $counter++;
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . 'Staff Report' . '.xls"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }
}
