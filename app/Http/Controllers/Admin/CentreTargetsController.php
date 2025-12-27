<?php

namespace App\Http\Controllers\Admin;

use App\Models\Centertarget;
use App\Models\Locations;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use DB, Auth, Validator, Config;
use App\Helpers\ACL;
use App\Helpers\Filters;
use Carbon\Carbon;


class CentreTargetsController extends Controller
{
    /**
     * Display a listing of Centre target.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('centre_targets_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'centertarget');

        $months[''] = 'All';
        $months_data = Config::get("constants.months_array");
        foreach ($months_data as $key => $value) {
            $months[$key] = $value;
        }

        $years[''] = 'All';
        $years_data = range(Carbon::now()->year, Carbon::now()->subYears(10)->year);
        foreach ($years_data as $key => $value) {
            $years[$value] = $value;
        }


        return view('admin.centre_targets.index', compact('filters', 'months', 'years'));
    }

    /**
     * Display a listing of Centre target.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;

        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'centertarget');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $centretarget = Centertarget::getBulkData($request->get('id'));
            if ($centretarget) {
                foreach ($centretarget as $centretarget) {
                    Centertarget::deleteRecord($centretarget->id);
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Centertarget::getTotalRecords($request, Auth::User()->account_id, $apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $centretargets = Centertarget::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $apply_filter);

        if ($centretargets) {
            $months_data = Config::get("constants.months_array");
            foreach ($centretargets as $centretarget) {
                $month = "constants.month_array[$centretarget->month]";
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $centretarget->id . '"/><span></span></label>',
                    'year' => $centretarget->year,
                    'month' => $months_data[$centretarget->month],
                    'created_at' => Carbon::parse($centretarget->created_at)->format('F j,Y h:i A'),
                    'actions' => view('admin.centre_targets.actions', compact('centretarget'))->render(),
                );

            }
        }


        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }


    /*
     * Show the form for creating new target.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('centre_targets_create')) {
            return abort(401);
        }

        $months[''] = 'Select a Month';
        $months_data = Config::get("constants.months_array");
        foreach ($months_data as $key => $value) {
            $months[$key] = $value;
        }

        $years[''] = 'Select a Year';
        $years_data = range(Carbon::now()->year, Carbon::now()->subYears(10)->year);
        foreach ($years_data as $key => $value) {
            $years[$value] = $value;
        }

        return view('admin.centre_targets.create', compact( 'months', 'years'));
    }

    /**
     * Load target centre
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function leadtargetcentre(Request $request)
    {
        $locationdata = Locations::LoadtargetLocationdata($request);

        $targetlocation = $locationdata['CenterTargetArray'];

        $center_target_status = $locationdata['center_target_status'];

        $center_target_working_days = $locationdata['center_target_working_days'];

        return response()->json(array(
            'status' => 1,
            'center_target_status' => $center_target_status,
            'center_target_working_days' => $center_target_working_days,
            'target_location' => view('admin.centre_targets.detail.table_content', compact('targetlocation'))->render(),
        ));
    }

    /*
     * Store the centre target
     */

    public function store(Request $request)
    {
        if (!Gate::allows('centre_targets_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $record = Centertarget::where(array(
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'account_id' => Auth::User()->account_id
        ))->first();

        if ($record) {
            $staff_target = Centertarget::updateRecord($record->id, $request, Auth::User()->account_id);
        } else {
            $staff_target = Centertarget::createRecord($request, Auth::User()->account_id);
        }

        if ($staff_target) {

            flash('Record has been created successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => 'Record has been created successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => 'Something went wrong, please try again later.',
            ));
        }
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
        ]);
    }

    /**
     * Show the form for editing center target.
     *
     * @param  int $id ,$request
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (!Gate::allows('centre_targets_edit')) {
            return abort(401);
        }

        $center_target = Centertarget::find($id);

        if (!$center_target) {
            return view('error', compact('lead_statuse'));
        }

        $months[''] = 'Select a Month';

        $months_data = Config::get("constants.months_array");
        foreach ($months_data as $key => $value) {
            $months[$key] = $value;
        }


        $years[''] = 'Select a Year';
        $years_data = range(Carbon::now()->year, Carbon::now()->subYears(10)->year);
        foreach ($years_data as $key => $value) {
            $years[$value] = $value;
        }

        return view('admin.centre_targets.edit', compact('center_target', 'months', 'years'));
    }

    /**
     * Update Centre target in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('centre_targets_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $record = Centertarget::where(array(
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'account_id' => Auth::User()->account_id
        ))->first();

        if ($record) {
            $staff_target = Centertarget::updateRecord($record->id, $request, Auth::User()->account_id);
        } else {
            $staff_target = Centertarget::createRecord($request, Auth::User()->account_id);
        }

        if ($staff_target) {

            flash('Record has been updated successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => 'Record has been updated successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => 'Something went wrong, please try again later.',
            ));
        }
    }

    /**
     * Show details of center target.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function display($id)
    {
        if (!Gate::allows('centre_targets_manage')) {
            return abort(401);
        }

        $centertarget = Centertarget::find($id);

        return view('admin.centre_targets.target_view', compact('centertarget'));

    }

    /**
     * Remove StaffTarget from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!Gate::allows('centre_targets_destroy')) {
            return abort(401);
        }
        Centertarget::deleteRecord($id);

        flash('Record has been deleted successfully.')->success()->important();

        return redirect()->route('admin.centre_targets.index');
    }


}
