<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUpdateCustomFormsRequest;
use App\Models\CustomFormFields;
use App\Models\CustomForms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use App\Helpers\Filters;

class CustomFormsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('custom_forms_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'custom_forms');

        return view('admin.custom_forms.index',compact('filters'));
    }

    /**
     * Display a listing of Lead_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;
        if($request->get('action')) {
            $action = $request->get('action');
            if(isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'custom_forms');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $CustomForms = CustomForms::getBulkData($request->get('id'));
            if ($CustomForms) {
                foreach ($CustomForms as $custom_form) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!CustomForms::isChildExists($custom_form->id, Auth::User()->account_id)) {
                        $custom_form->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = CustomForms::getTotalRecords($request, Auth::User()->account_id,$apply_filter);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $CustomForms = CustomForms::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if ($CustomForms) {
            foreach ($CustomForms as $custom_form) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $custom_form->id . '"/><span></span></label>',
                    'name' => $custom_form->name,
                    'form_type' => view('admin.custom_forms.Type', compact('custom_form'))->render(),
                    'created_at' => Carbon::parse($custom_form->created_at)->format('F j,Y h:i A'),
                    'status' => view('admin.custom_forms.status', compact('custom_form'))->render(),
                    'actions' => view('admin.custom_forms.actions', compact('custom_form'))->render(),
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
        if (!Gate::allows('custom_forms_create_general') && !Gate::allows('custom_forms_edit')) {
            return abort(401);
        }
        $data['custom_form_type'] = '0';
        $form = CustomForms::createForm(Auth::User()->account_id,$data);
        return redirect()->route('admin.custom_forms.edit', ['id' => $form->id]);
    }
    /**
     * Show the form for creating measurement new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_measurement()
    {
        if (!Gate::allows('custom_forms_create_measurement') && !Gate::allows('custom_forms_edit')) {
            return abort(401);
        }
        $data['custom_form_type'] = '1';
        $form = CustomForms::createForm(Auth::User()->account_id,$data);
        return redirect()->route('admin.custom_forms.edit', ['id' => $form->id]);
    }

    /**
     * Show the form for creating medical history from with new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_medical()
    {
        if (!Gate::allows('custom_forms_create_medical_history_form') && !Gate::allows('custom_forms_edit')) {
            return abort(401);
        }
        $data['custom_form_type'] = '2';
        $form = CustomForms::createForm(Auth::User()->account_id,$data);
        return redirect()->route('admin.custom_forms.edit', ['id' => $form->id]);
    }


    public function sortorder_save()
    {

        $custom_forms = DB::table('custom_forms')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_number', 'ASC')->get();
        $itemID = Input::get('itemID');
        $itemIndex = Input::get('itemIndex');
        if ($itemID) {
            foreach ($custom_forms as $custom_form) {
                $sort = DB::table('custom_forms')->where('id', '=', $itemID)->update(array('sort_number' => $itemIndex));
                $myarray = ['status' => "Data Sort Successfully"];
                return response()->json($myarray);
            }
        } else {
            $myarray = ['status' => "Data Not Sort"];
            return response()->json($myarray);
        }
    }

    public function sort_fields(Request $request, $id)
    {
        if (!Gate::allows('custom_forms_manage')) {
            return abort(401);
        }

        if (!CustomFormFields::sortFields($request, $id, Auth::User()->account_id, Auth::id())) {

            return response()->json(array("Unprocessable Entities" => ".", 'code' => 422), 422);
        }

        return response()->json(array("message" => "Records has been sorted successfully.", 'code' => 200), 200);
    }

    public function sortorder()
    {

        $custom_forms = DB::table('custom_forms')->where(['account_id' => Auth::User()->account_id])->orderby('sort_number', 'ASC')->get();
        return view('admin.custom_forms.sort', compact('custom_forms'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \App\Http\Requests\Admin\StoreUpdateCustomFormsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdateCustomFormsRequest $request)
    {
        if (!Gate::allows('custom_forms_manage')) {
            return abort(401);
        }

        if (CustomForms::createRecord($request, Auth::User()->account_id, Atuh::id())) {

            flash('Record has been created successfully.')->success()->important();
        } else {
            flash('Something went wrong, please try again later.')->error()->important();
        }

        return redirect()->route('admin.custom_forms.index');
    }


    /**
     * Show the form for editing Permission.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('custom_forms_edit') && !Gate::allows('custom_forms_create_measurement') && !Gate::allows('custom_forms_create_general')  ) {
            return abort(401);
        }

        $custom_form = CustomForms::get_all_fields_data($id);

        if (!$custom_form) {
            return view('error');
        }

        return view('admin.custom_forms.edit', compact('custom_form'));
    }

    /**
     * Update Permission in storage.
     *
     * @param  \App\Http\Requests\Admin\StoreUpdateCustomFormsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('custom_forms_manage')) {
            return abort(401);
        }

        if (CustomForms::updateRecord($id, $request, Auth::User()->account_id)) {

            flash('Record has been updated successfully.')->success()->important();
        } else {
            flash('Something went wrong, please try again later.')->error()->important();
        }

        return redirect()->route('admin.custom_forms.index');
    }

    public function form_update(Request $request, $id)
    {
        if (!Gate::allows('custom_forms_manage')) {
            return abort(401);
        }

        if ($custom_form = CustomForms::updateRecord($id, $request, Auth::User()->account_id, Auth::id())) {

            return response()->json($custom_form, 200);

        } else {
            return response()->json(array("message" => "Some went wrong, please try again later"), 400);
        }
    }


    public function create_field(Request $request, $id)
    {

        if (!Gate::allows('custom_forms_manage')) {
            return abort(401);
        }

        $data = CustomFormFields::createRecord($request, Auth::User()->account_id, Auth::id(), $id);
        if ($data) {
            return response()->json(array("request" => $request->all(), 'data' => $data));
        } else {
            response()->json(array("error" => $request->all(), 'id' => $id, 'data' => $data), 401);
        }

    }

    /**
     * update form field
     * @param Request $request
     * @param $form_id
     * @param $field_id
     * @return \Illuminate\Http\JsonResponse
     */

    public function update_field(Request $request, $form_id, $field_id)
    {
        if (!Gate::allows('custom_forms_manage')) {
            return abort(401);
        }
        $data = CustomFormFields::updateRecord($request, Auth::User()->account_id, Auth::id(), $form_id, $field_id);

        if ($data) {
            return response()->json(array("request" => $request->all(), 'data' => $data));
        } else {
            response()->json(array("error" => $request->all(), 'form_id' => $form_id, 'field_id' => $field_id, 'data' => $data), 401);
        }
    }

    public function delete_field(Request $request, $form_id, $field_id)
    {

        if (!Gate::allows('custom_forms_manage')) {
            return abort(401);
        }

        $custom_form_field = CustomFormFields::getData($field_id);

        if (!$custom_form_field) {

            return response()->json(array("message" => "Resource not found.", 'code' => 404), 404);
        }

        CustomFormFields::deleteRecord($form_id, $field_id);

        return response()->json(array("message" => "Record has been deleted successfully.", 'code' => 200), 200);

    }


    /**
     * Remove Permission from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('custom_forms_destroy')) {
            return abort(401);
        }

        $custom_form = CustomForms::getData($id);

        if (!$custom_form) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.custom_forms.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (CustomForms::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.custom_forms.index');
        }

        CustomForms::deleteRecord($id);

        flash('Record has been deleted successfully.')->success()->important();

        return redirect()->route('admin.custom_forms.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('custom_forms_inactive')) {
            return abort(401);
        }

        $custom_form = CustomForms::getData($id);

        if (!$custom_form) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.custom_forms.index');
        }
        CustomForms::inactivateRecord($id);

        flash('Record has been inactivated successfully.')->success()->important();

        return redirect()->route('admin.custom_forms.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('custom_forms_active')) {
            return abort(401);
        }



        $custom_form = CustomForms::getData($id);

        if (!$custom_form) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.custom_forms.index');
        }

        CustomForms::activateRecord($id);

        flash('Record has been inactivated successfully.')->success()->important();

        return redirect()->route('admin.custom_forms.index');
    }
}
