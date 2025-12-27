<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Models\Category;
use App\Models\Faqs;
use App\Helpers\Filters;
use App\Models\Regions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;

class FaqsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('faqs_manage')) {
            return abort(401);
        }

        $categories = Category::get()->pluck('name','id');
        $categories->prepend('All', '');

        $filters = Filters::all(Auth::User()->id, 'faqs');

        return view('admin.faqs.index', compact('filters', 'categories'));
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
                Filters::flush(Auth::User()->id, 'faqs');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $Faqs = Faqs::getBulkData($request->get('id'));
            if($Faqs) {
                foreach($Faqs as $faq) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    // if(!Faqs::isChildExists($faq->id, Auth::User()->account_id)) {
                        $faq->delete();
                    // }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Faqs::getTotalRecords($request, Auth::User()->account_id,$apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $Faqs = Faqs::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if($Faqs) {
            foreach($Faqs as $faq) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$faq->id.'"/><span></span></label>',
                    'category_id' => $faq->category->name,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'status' => view('admin.faqs.status', compact('faq'))->render(),
                    'actions' => view('admin.faqs.actions', compact('faq'))->render(),
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
        if (! Gate::allows('faqs_create')) {
            return abort(401);
        }

        $categories = Category::get()->pluck('name','id');
        $categories->prepend('Select a Category', '');

        return view('admin.faqs.create', compact('categories'));
    }

    public function sortorder_save(){

        if (! Gate::allows('faqs_sort')) {
            return abort(401);
        }

        $faq = DB::table('faqs')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_number', 'ASC')->get();
        $itemID=Input::get('itemID');
        $itemIndex=Input::get('itemIndex');
        if($itemID){
            foreach ($faq as $cit) {
                $sort=DB::table('faqs')->where('id', '=', $itemID)->update(array('sort_number' => $itemIndex));
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

        if (! Gate::allows('faqs_sort')) {
            return abort(401);
        }
        $faq = DB::table('faqs')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id,'slug'=>'custom'])->orderby('sort_number', 'ASC')->get();
        return view('admin.faqs.sort', compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('faqs_create')) {
            return abort(401);
        }
        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(Faqs::createRecord($request, Auth::User()->account_id)) {
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
            'question' => 'required',
            'answer' => 'required'
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
        if (! Gate::allows('faqs_edit')) {
            return abort(401);
        }

        $categories = Category::get()->pluck('name','id');
        $categories->prepend('Select a Category', '');

        $faq = Faqs::getData($id);
        if(!$faq) {
            return view('error');
        }

        return view('admin.faqs.edit',compact('faq', 'categories'));
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
        if (! Gate::allows('faqs_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(Faqs::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('faqs_destroy')) {
            return abort(401);
        }

        Faqs::DeleteRecord($id);

        return redirect()->route('admin.faqs.index');

    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('faqs_inactive')) {
            return abort(401);
        }
        Faqs::InactiveRecord($id);

        return redirect()->route('admin.faqs.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('faqs_active')) {
            return abort(401);
        }

        Faqs::activeRecord($id);
        
        return redirect()->route('admin.faqs.index');
    }
}
