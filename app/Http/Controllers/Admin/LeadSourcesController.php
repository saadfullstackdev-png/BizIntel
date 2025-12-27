<?php

namespace App\Http\Controllers\Admin;

use App\Models\LeadSources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;
use App\Helpers\Filters;

class LeadSourcesController extends Controller
{
    /**
     * Display a listing of Lead_source.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('lead_sources_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'lead_sources');

        return view('admin.lead_sources.index',compact('filters'));
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
                Filters::flush(Auth::User()->id, 'lead_sources');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $LeadSources = LeadSources::getBulkData($request->get('id'));
            if ($LeadSources) {
                foreach ($LeadSources as $LeadSource) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!LeadSources::isChildExists($LeadSource->id, Auth::User()->account_id)) {
                        $LeadSource->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = LeadSources::getTotalRecords($request, Auth::User()->account_id,$apply_filter);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $LeadSources = LeadSources::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if ($LeadSources) {
            foreach ($LeadSources as $lead_source) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $lead_source->id . '"/><span></span></label>',
                    'name' => $lead_source->name,
                    'status' => view('admin.lead_sources.status', compact('lead_source'))->render(),
                    'actions' => view('admin.lead_sources.actions', compact('lead_source'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating new Lead_source.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('lead_sources_create')) {
            return abort(401);
        }

        return view('admin.lead_sources.create');
    }

    public function sortorder()
    {

        if (!Gate::allows('lead_sources_sort')) {
            return abort(401);
        }

        $lead_source = DB::table('lead_sources')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderby('sort_no', 'ASC')->get();
        return view('admin.lead_sources.Sort', compact('lead_source'));
    }

    public function sortorder_save()
    {

        if (!Gate::allows('lead_sources_sort')) {
            return abort(401);
        }

        $lead_source = DB::table('lead_sources')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_no', 'ASC')->get();
        $itemID = Input::get('itemID');
        $itemIndex = Input::get('itemIndex');
        if ($itemID) {
            foreach ($lead_source as $lead) {
                $sort = DB::table('lead_sources')->where('id', '=', $itemID)->update(array('sort_no' => $itemIndex));
                $myarray = ['status' => "Data Sort Successfully"];
                return response()->json($myarray);
            }
        } else {
            $myarray = ['status' => "Data Not Sort"];
            return response()->json($myarray);
        }
    }

    /**
     * Store a newly created Lead_source in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('lead_sources_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (LeadSources::createRecord($request, Auth::User()->account_id)) {
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
     * Show the form for editing Lead_source.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('lead_sources_edit')) {
            return abort(401);
        }

        $lead_source = LeadSources::getData($id);

        if (!$lead_source) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.lead_sources.edit', compact('lead_source'));
    }

    /**
     * Update Lead_source in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('lead_sources_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (LeadSources::updateRecord($id, $request, Auth::User()->account_id)) {
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
     * Remove Lead_source from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('lead_sources_destroy')) {
            return abort(401);
        }

        LeadSources::DeleteRecord($id);

        return redirect()->route('admin.lead_sources.index');
    }
    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('lead_sources_inactive')) {
            return abort(401);
        }

        LeadSources::InactiveRecord($id);

        return redirect()->route('admin.lead_sources.index');
    }
    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('lead_sources_active')) {
            return abort(401);
        }
        LeadSources::activeRecord($id);

        return redirect()->route('admin.lead_sources.index');
    }

}
