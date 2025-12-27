<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Filters;
use App\Models\GlobalOperatorSettings;
use App\Models\Settings;
use App\Models\UserOperatorSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;

class UserOperatorSettingsController extends Controller
{
    /**
     * Display the list of operators.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('user_operator_settings_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'operators');

        return view('admin.user_operator_settings.index');
    }

    /**
     * Define datatable for listing of operators.
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
                Filters::flush(Auth::User()->id, 'operators');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        // Get Total Records
        $iTotalRecords = UserOperatorSettings::getTotalRecords($request, Auth::User()->account_id,$apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $Operators = UserOperatorSettings::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if($Operators) {
            foreach($Operators as $operator) {
                $records["data"][] = array(
                    'operator_name' => $operator->operator_name,
                    'username' => $operator->username,
                    'password' => '********',
                    'mask' => $operator->mask,
                    'test_mode' => $operator->test_mode==1?'Yes':'No',
                    'url' => $operator->url,
                    'string_1' => $operator->string_1,
                    'string_2' => $operator->string_2,
                    'actions' => view('admin.user_operator_settings.actions', compact('operator'))->render(),
                );
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
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
            'operator_id' => 'required',
        ]);
    }

    /**
     * Show the form for editing Permission.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('user_operator_settings_edit')) {
            return abort(401);
        }

        try {
            $id = decrypt($id);
        } catch (DecryptException $e) {
            return view('error', compact('lead_statuse'));
        }

        $user_operator_setting = UserOperatorSettings::getData($id);
//        /*
//         * Get Default Globar Operator
//         */
//        $GlobalOperatorSetting = UserOperatorSettings::getGlobalOperator($user_operator_setting->operator_id);
//
//        $GlobalOperatorSettings = UserOperatorSettings::getGlobalOperators();
        if (!$user_operator_setting) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.user_operator_settings.edit', compact('user_operator_setting'));
    }

    /**
     * Update Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('user_operator_settings_manage')) {
            return abort(401);
        }
        try {
            $id = decrypt($id);
        } catch (DecryptException $e) {
            return abort(401);
        }
//        $validator = $this->verifyFields($request);
//
//        if ($validator->fails()) {
//            return response()->json(array(
//                'status' => 0,
//                'message' => $validator->messages()->all(),
//            ));
//        }
        if (UserOperatorSettings::updateRecord($id, $request, Auth::User()->account_id)) {

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
     * Load Operator by ID
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function loadOperator(Request $request)
    {
        if (!Gate::allows('user_operator_settings_manage')) {
            return abort(401);
        }

        $GlobalOperatorSetting = UserOperatorSettings::getGlobalOperator($request->get('operator_id'));

        if ($GlobalOperatorSetting) {
            if ($GlobalOperatorSetting->password) {
                $GlobalOperatorSetting->password = '********';
            }

            return response()->json(array(
                'status' => 1,
                'operator_setting' => $GlobalOperatorSetting->toArray(),
            ));
        }

        return response()->json(array(
            'status' => 0,
            'message' => 'Something went wrong, please try again later.',
        ));
    }
}
