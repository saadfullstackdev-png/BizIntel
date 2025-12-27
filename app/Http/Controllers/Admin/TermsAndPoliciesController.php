<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Models\TermsAndPolicies;
use App\Helpers\Filters;
use App\Models\Regions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;

class TermsAndPoliciesController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('termsandpolicies_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'termsandpolicies');

        return view('admin.termsandpolicies.index');
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
                Filters::flush(Auth::User()->id, 'termsandpolicies');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $TermsAndPolicies = TermsAndPolicies::getBulkData($request->get('id'));
            if($TermsAndPolicies) {
                foreach($TermsAndPolicies as $termsandpolicy) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    // if(!TermsAndPolicies::isChildExists($termsandpolicy->id, Auth::User()->account_id)) {
                        $termsandpolicy->delete();
                    // }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = TermsAndPolicies::getTotalRecords($request, Auth::User()->account_id,$apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $TermsAndPolicies = TermsAndPolicies::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        $Regions = Regions::getAllRecordsDictionary(Auth::User()->account_id);

        if($TermsAndPolicies) {
            foreach($TermsAndPolicies as $termsandpolicy) {
               
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$termsandpolicy->id.'"/><span></span></label>',
                    'name' => $termsandpolicy->name,
                    'description' => (strlen($termsandpolicy->description)>70) ? substr($termsandpolicy->description, 0, 70) . '...' : $termsandpolicy->description ,
                    'status' => view('admin.termsandpolicies.status', compact('termsandpolicy'))->render(),
                    'actions' => view('admin.termsandpolicies.actions', compact('termsandpolicy'))->render(),
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
        if (! Gate::allows('termsandpolicies_create')) {
            return abort(401);
        }

        return view('admin.termsandpolicies.create');
    }

    public function sortorder_save(){

        if (! Gate::allows('termsandpolicies_sort')) {
            return abort(401);
        }

        $termsandpolicy = DB::table('termsandpolicies')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_number', 'ASC')->get();
        $itemID=Input::get('itemID');
        $itemIndex=Input::get('itemIndex');
        if($itemID){
            foreach ($termsandpolicy as $cit) {
                $sort=DB::table('termsandpolicies')->where('id', '=', $itemID)->update(array('sort_number' => $itemIndex));
                $myarray=['status'=>"Data Sort Successfully"];
                return response()->json($myarray);
            }
        }
        else{
            $myarray=['status'=>"Data Not Sort"];
            return response()->json($myarray);
        }
    }

    public function sortorder(){

        if (! Gate::allows('termsandpolicies_sort')) {
            return abort(401);
        }
        $termsandpolicy = DB::table('termsandpolicies')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id,'slug'=>'custom'])->orderby('sort_number', 'ASC')->get();
        return view('admin.termsandpolicies.sort', compact('termsandpolicy'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('termsandpolicies_create')) {
            return abort(401);
        }
        // dd($request);
        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(TermsAndPolicies::createRecord($request, Auth::User()->account_id)) {
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
            'description' => 'required',
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
        if (! Gate::allows('termsandpolicies_edit')) {
            return abort(401);
        }

        $termsandpolicy = TermsAndPolicies::getData($id);
        if(!$termsandpolicy) {
            return view('error');
        }

        return view('admin.termsandpolicies.edit',compact('termsandpolicy'));
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
        if (! Gate::allows('termsandpolicies_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(TermsAndPolicies::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('termsandpolicies_destroy')) {
            return abort(401);
        }

        TermsAndPolicies::DeleteRecord($id);

        return redirect()->route('admin.termsandpolicies.index');

    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('termsandpolicies_inactive')) {
            return abort(401);
        }
        TermsAndPolicies::InactiveRecord($id);

        return redirect()->route('admin.termsandpolicies.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('termsandpolicies_active')) {
            return abort(401);
        }

        TermsAndPolicies::activeRecord($id);
        
        return redirect()->route('admin.termsandpolicies.index');
    }
}
