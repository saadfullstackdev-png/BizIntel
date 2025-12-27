<?php

namespace App\Http\Controllers\Admin;

use App\Models\SMSTemplates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Validator;
use App\Helpers\Filters;

class SMSTemplatesController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('sms_templates_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'sms_templates');

        return view('admin.sms_templates.index');
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
                Filters::flush(Auth::User()->id, 'sms_templates');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $SMSTemplates = SMSTemplates::getBulkData($request->get('id'));
            if($SMSTemplates) {
                foreach($SMSTemplates as $SMSTemplate) {
                    $SMSTemplate->delete();
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = SMSTemplates::getTotalRecords($request, Auth::User()->account_id,$apply_filter);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $SMSTemplates = SMSTemplates::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if($SMSTemplates) {
            foreach($SMSTemplates as $sms_template) {
                $records["data"][] = array(
                    'name' => $sms_template->name,
                    'content' => substr($sms_template->content, 0, 70) . '...',
                    'slug' => $sms_template->slug,
                    'status' => view('admin.sms_templates.status', compact('sms_template'))->render(),
                    'actions' => view('admin.sms_templates.actions', compact('sms_template'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }


    /**
     * Show the form for creating new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('sms_templates_manage')) {
            return abort(401);
        }
        return view('admin.sms_templates.create');
    }


    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('sms_templates_manage')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(SMSTemplates::createRecord($request, Auth::User()->account_id)) {
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
            'content' => 'required',
        ]);
    }


    /**
     * Show the form for editing Permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('sms_templates_edit')) {
            return abort(401);
        }

        $sms_template = SMSTemplates::getData($id);

        if(!$sms_template) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.sms_templates.edit', compact('sms_template'));
    }

    /**
     * Update Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('sms_templates_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(SMSTemplates::updateRecord($id, $request, Auth::User()->account_id)) {
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
     * Remove Permission from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('sms_templates_manage')) {
            return abort(401);
        }

        $sms_template = SMSTemplates::getData($id);

        if(!$sms_template) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.sms_templates.index');
        }

        $sms_template->delete();

        flash('Record has been deleted successfully.')->success()->important();

        return redirect()->route('admin.sms_templates.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('sms_templates_inactive')) {
            return abort(401);
        }

        $sms_template = SMSTemplates::getData($id);

        if(!$sms_template) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.sms_templates.index');
        }

        $sms_template->update(['active' => 0]);

        flash('Record has been inactivated successfully.')->success()->important();

        return redirect()->route('admin.sms_templates.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('sms_templates_active')) {
            return abort(401);
        }

        $sms_template = SMSTemplates::getData($id);

        if(!$sms_template) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.sms_templates.index');
        }

        $sms_template->update(['active' => 1]);

        flash('Record has been inactivated successfully.')->success()->important();

        return redirect()->route('admin.sms_templates.index');
    }

}
