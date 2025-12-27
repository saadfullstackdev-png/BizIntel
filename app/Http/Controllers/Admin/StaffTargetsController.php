<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Widgets\LocationsWidget;
use App\Helpers\Widgets\StaffTargetsWidget;
use App\Models\Cities;
use App\Models\Doctors;
use App\Models\Locations;
use App\Models\StaffTargets;
use App\Models\Regions;
use App\Models\ServiceHasStaffTargets;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB, Auth, Validator, Config;
use Carbon\Carbon;
use App\Models\UserHasStaffTargets;
use App\Helpers\ACL;
use App\Helpers\Filters;

class StaffTargetsController extends Controller
{
    /**
     * Display a listing of StaffTarget.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('staff_targets_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'staff_target_location');

        $cities = Cities::getActiveSortedFeatured(ACL::getUserCities());
        $cities->prepend('Select a City', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        return view('admin.staff_targets.index', compact('cities', 'regions','filters'));
    }

    /**
     * Display a listing of Lead_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {

        $apply_filter = false;
        if($request->get('action')) {
            $action = $request->get('action');
            if(isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'staff_target_location');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        // Get Total Records
        $iTotalRecords = Locations::getTotalRecords_target($request, Auth::User()->account_id,$apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $StaffTargets = Locations::getRecords_target($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        $Cities = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $Regions = Regions::getAllRecordsDictionary(Auth::User()->account_id);

        if ($StaffTargets) {
            foreach ($StaffTargets as $staff_target) {
                $records["data"][] = array(
                    'name' => $staff_target->name,
                    'city' => (array_key_exists($staff_target->city_id, $Cities)) ? $Cities[$staff_target->city_id]->name : 'N/A',
                    'region' => (array_key_exists($staff_target->region_id, $Regions)) ? $Regions[$staff_target->region_id]->name : 'N/A',
                    'created_at' => Carbon::parse($staff_target->created_at)->format('F j,Y h:i A'),
                    'actions' => view('admin.staff_targets.actions', compact('staff_target'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show details.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        if (!Gate::allows('staff_targets_manage')) {
            return abort(401);
        }

        $location = Locations::findOrFail($id);

        $months[''] = 'All';
        $months_data = Config::get("constants.months_array");
        foreach($months_data as $key => $value) {
            $months[$key] = $value;
        }

        $years[''] = 'All';
        $years_data = range(Carbon::now()->year, Carbon::now()->subYears(10)->year);
        foreach($years_data as $year) {
            $years[$year] = $year;
        }

        $applicationuser = User::getAllActiveEmployeeRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $practioners = User::getAllActivePractionersRecords(Auth::User()->account_id, ACL::getUserCentres())->pluck('name', 'id');
        $select_All = array(''=>'Select a Staff Member');

        $employees = ($select_All+$practioners->toArray()+$applicationuser->toArray());

        return view('admin.staff_targets.detail.index', compact( 'employees', 'location', 'months', 'years'));
    }

    /**
     * Show details of staff target.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function targetView($id)
    {
        if (!Gate::allows('staff_targets_manage')) {
            return abort(401);
        }

        $staff_target = StaffTargets::getData($id);

        return view('admin.staff_targets.detail.target_view', compact('staff_target'));
    }

    /**
     * Display a listing of Lead_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function detailDatatable(Request $request)
    {
        $records = array();
        $records["data"] = array();

        // Get Total Records
        $iTotalRecords = StaffTargets::getTotalRecords($request, Auth::User()->account_id);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $StaffTargets = StaffTargets::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        $Locations = Locations::getAllRecordsDictionary(Auth::User()->account_id);
        $Users = User::getAllRecords(Auth::User()->account_id)->getDictionary();

        if ($StaffTargets) {
            foreach ($StaffTargets as $staff_target) {
                $records["data"][] = array(
                    'location_id' => $Locations[$staff_target->location_id]->name,
                    'staff_id' => $Users[$staff_target->staff_id]->name,
                    'month' => Config::get('constants.months_array')[$staff_target->month],
                    'year' => $staff_target->year,
                    'total_amount' => number_format($staff_target->total_amount, 2),
                    'total_services' => number_format($staff_target->total_services),
                    'created_at' => Carbon::parse($staff_target->created_at)->format('F j,Y h:i A'),
                    'actions' => view('admin.staff_targets.detail.actions', compact('staff_target'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating new StaffTarget.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($location_id = false)
    {
        if (!Gate::allows('staff_targets_create')) {
            return abort(401);
        }

        if(!$location_id) {
            // Invalid location is provided
            return abort(401);
        }

        $location = Locations::findOrFail($location_id);

        $staffs = array();

        $doctors = Doctors::getActiveOnly($location_id, Auth::User()->account_id, false, false);
        if($doctors->count()) {
            foreach($doctors as $doctor) {
                $staffs[$doctor->user_id] = $doctor;
            }
        }

        $staff_members = User::getActiveOnly($location_id, Auth::User()->account_id, false, false);
        if($staff_members->count()) {
            foreach($staff_members as $staff_member) {
                $staffs[$staff_member->user_id] = $staff_member;
            }
        }

        $months[''] = 'Select a Month';

        $months_data = Config::get("constants.months_array");
        foreach($months_data as $key => $value) {
            $months[$key] = $value;
        }


        $years[''] = 'Select a Year';
        $years_data = range(Carbon::now()->year, Carbon::now()->subYears(10)->year);
        foreach($years_data as $key => $value) {
            $years[$value] = $value;
        }

        return view('admin.staff_targets.detail.create', compact('location', 'staffs', 'months', 'years'));
    }

    /**
     * Store a newly created StaffTarget in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('staff_targets_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'location_id' => $request->get('location_id'),
                'message' => $validator->messages()->all(),
            ));
        }

        $record = StaffTargets::where(array(
            'location_id' => $request->get('location_id'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'staff_id' => $request->get('staff_id'),
            'account_id' => Auth::User()->account_id
        ))->first();

        if ($record) {
            $staff_target = StaffTargets::updateRecord($record->id, $request, Auth::User()->account_id);
        } else {
            $staff_target = StaffTargets::createRecord($request, Auth::User()->account_id);
        }

        if ($staff_target) {

            flash('Record has been created successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'location_id' => $request->get('location_id'),
                'message' => 'Record has been created successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'location_id' => $request->get('location_id'),
                'message' => 'Something went wrong, please try again later.',
            ));
        }
    }

    /**
     * Load target services by location
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function loadTargetServices(Request $request)
    {
        $staff = User::find($request->staff_id);
        if($staff->user_type_id == Config::get('constants.application_user_id')) {
            dd("Staff Target");
            /*
             * Staff Targets
             */
//            echo 'B'; exit;
            $serviceIds = LocationsWidget::loadEndServiceByLocation($request->get("location_id"), Auth::User()->account_id);
        } else {
            /*
             * Doctor Targets
             */
            $serviceIds = LocationsWidget::loadAppointmentServiceByLocationDoctor($request->get("location_id"), $request->get("staff_id"), Auth::User()->account_id, true);
        }

        $staffTargetServices = StaffTargets::getStaffTargetServices($request, $serviceIds, Auth::User()->account_id);

        return response()->json(array(
            'status' => 1,
            'target_services_count' => count($staffTargetServices['target_services']),
            'table_content' => view('admin.staff_targets.detail.table_content', compact('staffTargetServices'))->render(),
            'total_amount' => $staffTargetServices['total_amount'],
            'total_services' => $staffTargetServices['total_services'],
        ));
    }

    /**
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'year' => 'required',
            'month' => 'required',
            'staff_id' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'total_services' => 'required|numeric',
        ]);
    }

    /**
     * Show the form for editing StaffTarget.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('staff_targets_edit')) {
            return abort(401);
        }

        $staff_target = StaffTargets::getData($id);

        if (!$staff_target) {
            return view('error', compact('lead_statuse'));
        }

        $location = Locations::findOrFail($staff_target->location_id);

        $staffs = array();

        $doctors = Doctors::getActiveOnly($staff_target->location_id, Auth::User()->account_id, false, false);
        if($doctors->count()) {
            foreach($doctors as $doctor) {
                $staffs[$doctor->user_id] = $doctor;
            }
        }

        $staff_members = User::getActiveOnly($staff_target->location_id, Auth::User()->account_id, false, false);
        if($staff_members->count()) {
            foreach($staff_members as $staff_member) {
                $staffs[$staff_member->user_id] = $staff_member;
            }
        }

        $months[''] = 'Select a Month';

        $months_data = Config::get("constants.months_array");
        foreach($months_data as $key => $value) {
            $months[$key] = $value;
        }


        $years[''] = 'Select a Year';
        $years_data = range(Carbon::now()->year, Carbon::now()->subYears(10)->year);
        foreach($years_data as $key => $value) {
            $years[$value] = $value;
        }

        return view('admin.staff_targets.detail.edit', compact('staff_target', 'location', 'staffs', 'months', 'years'));
    }

    /**
     * Update StaffTarget in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('staff_targets_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $record = StaffTargets::where(array(
            'location_id' => $request->get('location_id'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'staff_id' => $request->get('staff_id'),
            'account_id' => Auth::User()->account_id
        ))->first();

        if ($record) {
            $staff_target = StaffTargets::updateRecord($record->id, $request, Auth::User()->account_id);
        } else {
            $staff_target = StaffTargets::createRecord($request, Auth::User()->account_id);
        }

        if ($staff_target) {

            flash('Record has been updated successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'location_id' => $request->get('location_id'),
                'message' => 'Record has been updated successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'location_id' => $request->get('location_id'),
                'message' => 'Something went wrong, please try again later.',
            ));
        }
    }
    /**
     * Remove StaffTarget from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!Gate::allows('staff_targets_destroy')) {
            return abort(401);
        }
        StaffTargets::deleteRecord($id);

        if($request->get('location_id')) {
            return redirect()->route('admin.staff_targets.detail', [$request->get('location_id')]);
        } else {
            return redirect()->route('admin.staff_targets.index');
        }
    }

}
