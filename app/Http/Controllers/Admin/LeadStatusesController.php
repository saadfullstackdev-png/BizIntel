<?php

namespace App\Http\Controllers\Admin;

use App\Models\LeadStatuses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;
use App\Helpers\Filters;

class LeadStatusesController extends Controller
{
    /**
     * Display a listing of Lead_statuse.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('lead_statuses_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'lead_statuses');

        return view('admin.lead_statuses.index');
    }

    /**
     * Display a listing of Lead_statuse
     *
     *  param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;
        if($request->get('action')) {
            $action = $request->get('action');
            if(isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'lead_statuses');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $LeadStatuses = LeadStatuses::getBulkData($request->get('id'));
            if($LeadStatuses) {
                foreach($LeadStatuses as $LeadStatus) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!LeadStatuses::isChildExists($LeadStatus->id, Auth::User()->account_id)) {
                        $LeadStatus->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = LeadStatuses::getTotalRecords($request, Auth::User()->account_id,$apply_filter);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $allLeadStatuses = LeadStatuses::getAllRecordsDictionary(Auth::User()->account_id);

        $LeadStatuses = LeadStatuses::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if($LeadStatuses) {
            foreach($LeadStatuses as $lead_statuse) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$lead_statuse->id.'"/><span></span></label>',
                    'name' => $lead_statuse->name,
                    'parent_id' => ($lead_statuse->parent_id && array_key_exists($lead_statuse->parent_id, $allLeadStatuses)) ? $allLeadStatuses[$lead_statuse->parent_id]->name : '-',
                    'is_comment' => ($lead_statuse->is_comment) ? 'Yes' : 'No',
                    'is_default' => ($lead_statuse->is_default) ? 'Yes' : 'No',
                    'is_arrived' => ($lead_statuse->is_arrived) ? 'Yes' : 'No',
                    'is_converted' => ($lead_statuse->is_converted) ? 'Yes' : 'No',
                    'is_junk' => ($lead_statuse->is_junk) ? 'Yes' : 'No',
                    'status' => view('admin.lead_statuses.status', compact('lead_statuse'))->render(),
                    'actions' => view('admin.lead_statuses.actions', compact('lead_statuse'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating new Lead_statuse.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('lead_statuses_create')) {
            return abort(401);
        }

        $lead_statuse = new \stdClass();
        $lead_statuse->is_default = 0;
        $lead_statuse->is_arrived = 0;
        $lead_statuse->is_converted = 0;
        $lead_statuse->is_junk = 0;

        $parentLeadStatuses = LeadStatuses::getParentRecords('Parent Group', Auth::User()->account_id, false, true);

        return view('admin.lead_statuses.create', compact('parentLeadStatuses', 'lead_statuse'));
    }

    public function sortorder(){

        if (! Gate::allows('lead_statuses_sort')) {
            return abort(401);
        }

        $lead_status = DB::table('lead_statuses')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderby('sort_no', 'ASC')->get();
        return view('admin.lead_statuses.Sort',compact('lead_status'));
    }

    public function sortorder_save(){

        if (! Gate::allows('lead_statuses_sort')) {
            return abort(401);
        }


        $lead_status = DB::table('lead_statuses')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_no', 'ASC')->get();
        $itemID=Input::get('itemID');
        $itemIndex=Input::get('itemIndex');
        if($itemID){
            foreach ($lead_status as $lead) {
                $sort=DB::table('lead_statuses')->where('id', '=', $itemID)->update(array('sort_no' => $itemIndex));
                $myarray=['status'=>"Data Sort Successfully"];
                return response()->json($myarray);
            }
        }
        else{
            $myarray=['status'=>"Data Not Sort"];
            return response()->json($myarray);
        }
    }

    /**
     * Store a newly created Lead_statuse in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('lead_statuses_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(LeadStatuses::createRecord($request, Auth::User()->account_id)) {
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
     * Show the form for editing Lead_statuse.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('lead_statuses_edit')) {
            return abort(401);
        }

        $lead_statuse = LeadStatuses::getData($id);

        if(!$lead_statuse) {
            return view('error', compact('lead_statuse'));
        }

        $parentLeadStatuses = LeadStatuses::getParentRecords('Parent Group', Auth::User()->account_id, $lead_statuse->id, true);

        return view('admin.lead_statuses.edit', compact('lead_statuse', 'parentLeadStatuses'));
    }

    /**
     * Update Lead_statuse in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('lead_statuses_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(LeadStatuses::updateRecord($id, $request, Auth::User()->account_id)) {
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
     * Remove Lead_statuse from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('lead_statuses_destroy')) {
            return abort(401);
        }
        LeadStatuses::DeleteRecord($id);

        return redirect()->route('admin.lead_statuses.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('lead_statuses_inactive')) {
            return abort(401);
        }
        LeadStatuses::InactiveRecord($id);

        return redirect()->route('admin.lead_statuses.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('lead_statuses_active')) {
            return abort(401);
        }

        LeadStatuses::activeRecord($id);

        return redirect()->route('admin.lead_statuses.index');
    }

}
