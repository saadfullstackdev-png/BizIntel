<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentStatuses;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Validator;
use App\Helpers\Filters;

class AppointmentStatusesController extends Controller
{
    /**
     * Display a listing of Appointment_statuse.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('appointment_statuses_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'appointment_statuses');

        return view('admin.appointment_statuses.index',compact('filters'));
    }

    /**
     * Display a listing of Appointment_statuse.
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
                Filters::flush(Auth::User()->id, 'appointment_statuses');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $AppointmentStatuses = AppointmentStatuses::getBulkData($request->get('id'));
            if ($AppointmentStatuses) {
                foreach ($AppointmentStatuses as $appointment) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!AppointmentStatuses::isChildExists($appointment->id, Auth::User()->account_id)) {
                        $appointment->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = AppointmentStatuses::getTotalRecords($request, Auth::User()->account_id,$apply_filter);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $allAppointmentStatuses = AppointmentStatuses::getAllRecordsDictionary(Auth::User()->account_id);

        $AppointmentStatuses = AppointmentStatuses::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if($AppointmentStatuses) {
            foreach($AppointmentStatuses as $appointment_statuse) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$appointment_statuse->id.'"/><span></span></label>',
                    'name' => $appointment_statuse->name,
                    'parent_id' => ($appointment_statuse->parent_id && array_key_exists($appointment_statuse->parent_id, $allAppointmentStatuses)) ? $allAppointmentStatuses[$appointment_statuse->parent_id]->name : '-',
                    'is_comment' => ($appointment_statuse->is_comment) ? 'Yes' : 'No',
                    'allow_message' => (!$appointment_statuse->parent_id) ? ($appointment_statuse->allow_message) ? 'Yes' : 'No' : '-',
                    'is_default' => (!$appointment_statuse->parent_id) ? ($appointment_statuse->is_default) ? 'Yes' : 'No' : '-',
                    'is_arrived' => ($appointment_statuse->is_arrived) ? 'Yes' : 'No',
                    'is_cancelled' => ($appointment_statuse->is_cancelled) ? 'Yes' : 'No',
                    'is_unscheduled' => ($appointment_statuse->is_unscheduled) ? 'Yes' : 'No',
                    'status' => view('admin.appointment_statuses.status', compact('appointment_statuse'))->render(),
                    'actions' => view('admin.appointment_statuses.actions', compact('appointment_statuse'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating new Appointment_statuse.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('appointment_statuses_create')) {
            return abort(401);
        }

        $appointment_statuse = new \stdClass();
        $appointment_statuse->is_default = 0;
        $appointment_statuse->is_arrived = 0;
        $appointment_statuse->is_cancelled = 0;
        $appointment_statuse->is_unscheduled = 0;

        $parentAppointmentStatuses = AppointmentStatuses::getParentRecords('Parent Group', Auth::User()->account_id, false, true);

        return view('admin.appointment_statuses.create', compact('parentAppointmentStatuses', 'appointment_statuse'));
    }

    /**
     * Store a newly created Appointment_statuse in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('appointment_statuses_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (AppointmentStatuses::createRecord($request, Auth::User()->account_id)) {
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
            'name' => 'required',
        ]);
    }


    /**
     * Show the form for editing Appointment_statuse.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('appointment_statuses_edit')) {
            return abort(401);
        }

        $appointment_statuse = AppointmentStatuses::getData($id);

        if (!$appointment_statuse) {
            return view('error', compact('appointment_statuse'));
        }

        $parentAppointmentStatuses = AppointmentStatuses::getParentRecords('Parent Group', Auth::User()->account_id, $appointment_statuse->id, true);

        return view('admin.appointment_statuses.edit', compact('appointment_statuse', 'parentAppointmentStatuses'));
    }

    /**
     * Update Appointment_statuse in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('appointment_statuses_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (AppointmentStatuses::updateRecord($id, $request, Auth::User()->account_id)) {
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
     * Remove Appointment_statuse from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('appointment_statuses_destroy')) {
            return abort(401);
        }
        AppointmentStatuses::deleteRecord($id);

        return redirect()->route('admin.appointment_statuses.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('appointment_statuses_inactive')) {
            return abort(401);
        }
        AppointmentStatuses::inactiveRecord($id);

        return redirect()->route('admin.appointment_statuses.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('appointment_statuses_active')) {
            return abort(401);
        }
        AppointmentStatuses::activeRecord($id);

        return redirect()->route('admin.appointment_statuses.index');
    }

}
