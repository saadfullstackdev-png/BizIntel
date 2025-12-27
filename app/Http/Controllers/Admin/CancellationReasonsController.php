<?php

namespace App\Http\Controllers\Admin;

use App\Models\CancellationReasons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;

class CancellationReasonsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('cancellation_reasons_manage')) {
            return abort(401);
        }
        return view('admin.cancellation_reasons.index', compact('cancellation_reasons'));
    }

    /**
     * Display a listing of Lead_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $CancellationReasons = CancellationReasons::getBulkData($request->get('id'));
            if ($CancellationReasons) {
                foreach ($CancellationReasons as $CancellationReason) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!CancellationReasons::isChildExists($CancellationReason->id, Auth::User()->account_id)) {
                        $CancellationReason->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = CancellationReasons::getTotalRecords($request, Auth::User()->account_id);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $CancellationReasons = CancellationReasons::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($CancellationReasons) {
            foreach($CancellationReasons as $cancellation_reason) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$cancellation_reason->id.'"/><span></span></label>',
                    'name' => $cancellation_reason->name,
                    'actions' => view('admin.cancellation_reasons.actions', compact('cancellation_reason'))->render(),
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
        if (! Gate::allows('cancellation_reasons_manage')) {
            return abort(401);
        }
        return view('admin.cancellation_reasons.createTo');
    }

    public function sortorder(){

        $cancellation_reasons = DB::table('cancellation_reasons')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderby('sort_no', 'ASC')->get();
        return view('admin.cancellation_reasons.Sort',compact('cancellation_reasons'));
    }

    public function sortorder_save(){

        $cancellation_reasons = DB::table('cancellation_reasons')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_no', 'ASC')->get();
        $itemID=Input::get('itemID');
        $itemIndex=Input::get('itemIndex');
        if($itemID){
            foreach ($cancellation_reasons as $lead) {
                $sort=DB::table('cancellation_reasons')->where('id', '=', $itemID)->update(array('sort_no' => $itemIndex));
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
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('cancellation_reasons_manage')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (CancellationReasons::createRecord($request, Auth::User()->account_id)) {
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
     * Show the form for editing Permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('cancellation_reasons_manage')) {
            return abort(401);
        }

        $cancellation_reason = CancellationReasons::getData($id);

        if (!$cancellation_reason) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.cancellation_reasons.editTo', compact('cancellation_reason'));
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
        if (! Gate::allows('cancellation_reasons_manage')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (CancellationReasons::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('cancellation_reasons_manage')) {
            return abort(401);
        }
        CancellationReasons::deleteRecord($id);

        return redirect()->route('admin.cancellation_reasons.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('cancellation_reasons_manage')) {
            return abort(401);
        }
        CancellationReasons::inactiveRecord($id);

        return redirect()->route('admin.cancellation_reasons.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('cancellation_reasons_manage')) {
            return abort(401);
        }
        CancellationReasons::activeRecord($id);

        return redirect()->route('admin.cancellation_reasons.index');
    }

}
