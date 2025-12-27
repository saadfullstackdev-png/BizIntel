<?php

namespace App\Http\Controllers\admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;
use App\Models\UserLoginLogs;
use App\Helpers\Filters;
use PHPUnit\Util\Filter;
use Carbon\Carbon;

class UserLoginLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('UserLoginLogs_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'userLogingLogs');

        return view('admin.user_login_logs.index', compact('filters'));

    }

    /**
     * Display a listing of the logs.
     *
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;

        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'userLogingLogs');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        // Get Total Records
        $iTotalRecords = UserLoginLogs::getTotalRecords($request, Auth::User()->account_id, $apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $UserLoginLogs = UserLoginLogs::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $apply_filter);

        if ($UserLoginLogs) {
            foreach ($UserLoginLogs as $user_login_log) {
                $User = User::find($user_login_log->user_id);
                $records["data"][] = array(
                    'id' => $user_login_log->id,
                    'user_id' => $User->name,
                    'user_email' => $User->email,
                    'user_ip' => $user_login_log->user_ip,
                    'location' => $user_login_log->location,
                    'machine_name' => $user_login_log->machine_name,
                    'browser' => $user_login_log->browser,
                    'os' => $user_login_log->os,
                    'longitude' => $user_login_log->longitude,
                    'latitude' => $user_login_log->latitude,
                    'country' => $user_login_log->country,
                    'country_code' => $user_login_log->country_code,
                    'login_time' => $user_login_log->login_time ? Carbon::parse($user_login_log->login_time)->format('F j,Y h:i A') : '',
                    'logout_time' => $user_login_log->logout_time ? Carbon::parse($user_login_log->logout_time)->format('F j,Y h:i A') : '',
                    'created_at' => $user_login_log->created_at ? Carbon::parse($user_login_log->created_at)->format('F j,Y h:i A') : '',
                    'actions' => ''
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }
}
