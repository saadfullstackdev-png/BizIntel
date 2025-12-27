<?php

namespace App\Models;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Helpers\GeneralFunctions;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Auth;
use PHPUnit\Util\Filter;
use Config;


class UserLoginLogs extends BaseModal
{

    protected $fillable = ['id', 'user_ip', 'user_mac', 'location', 'machine_name', 'browser', 'os', 'longitude', 'latitude', 'country', 'country_code', 'session_id', 'login_time', 'logout_time', 'account_id', 'created_at', 'updated_at'];

    protected $table = 'user_login_logs';

    protected static $_table = 'user_login_logs';

    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $account_id = false, $apply_filter = false)
    {
        $where = self::filters_userLoginLogs($request, $account_id, $apply_filter);

        if (count($where)) {
            return User::join('user_login_logs','users.id','=','user_login_logs.user_id')->where($where)->count();
        }
    }

    /**
     * Get Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $iDisplayStart Start Index
     * @param (int) $iDisplayLength Total Records Length
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false, $apply_filter)
    {

        $where = Self::filters_userLoginLogs($request, $account_id, $apply_filter);

        $orderBy = 'created_at';
        $order = 'desc';

        if ($request->get('order')) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            if ($orderBy == 'created_at') {
                $orderBy = 'user_login_logs.created_at';
            }
            $order = $request->get('order')[0]['dir'];

            Filters::put(Auth::User()->id, 'userLogingLogs', 'order_by', $orderBy);
            Filters::put(Auth::User()->id, 'userLogingLogs', 'order', $order);
        } else {
            if (
                Filters::get(Auth::User()->id, 'userLogingLogs', 'order_by')
                && Filters::get(Auth::User()->id, 'userLogingLogs', 'order')
            ) {
                $orderBy = Filters::get(Auth::User()->id, 'userLogingLogs', 'order_by');
                $order = Filters::get(Auth::User()->id, 'userLogingLogs', 'order');

                if ($orderBy == 'created_at') {
                    $orderBy = 'user_login_logs.created_at';
                }
            } else {
                $orderBy = 'created_at';
                $order = 'desc';
                if ($orderBy == 'created_at') {
                    $orderBy = 'user_login_logs.created_at';
                }

                Filters::put(Auth::User()->id, 'userLogingLogs', 'order_by', $orderBy);
                Filters::put(Auth::User()->id, 'userLogingLogs', 'order', $order);
            }
        }
        if (count($where)) {
            return User::join('user_login_logs','users.id','=','user_login_logs.user_id')->where($where)
                ->orderby($orderBy, $order)
                ->limit($iDisplayLength)->offset($iDisplayStart)->get();
        }
    }

    static public function filters_userLoginLogs($request, $account_id, $apply_filter)
    {
        $where = array();

        if ($account_id) {
            $where[] = array(
                'user_login_logs.account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::user()->id, 'userLogingLogs', 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'userLogingLogs', 'account_id');
            } else {
                if (Filters::get(Auth::user()->id, 'userLogingLogs', 'account_id')) {
                    $where[] = array(
                        'user_login_logs.account_id',
                        '=',
                        Filters::get(Auth::user()->id, 'userLogingLogs', 'account_id')
                    );
                }
            }
        }

        if ($request->get('name') && $request->get('name') != '') {
            $where[] = array(
                'users.name',
                'like',
                '%' . $request->get('name') . '%'
            );
            Filters::put(Auth::user()->id, 'userLogingLogs', 'name', $request->get('name'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'userLogingLogs', 'name');
            } else {
                if (Filters::get(Auth::user()->id, 'userLogingLogs', 'name')) {
                    $where[] = array(
                        'users.name',
                        'like',
                        '%' . Filters::get(Auth::user()->id, 'userLogingLogs', 'name') . '%'
                    );
                }
            }
        }
        if ($request->get('created_from') && $request->get('created_from') != '') {
            $where[] = array(
                'user_login_logs.created_at',
                '>=',
                $request->get('created_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, 'userLogingLogs', 'created_from', $request->get('created_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'userLogingLogs', 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, 'userLogingLogs', 'created_from')) {
                    $where[] = array(
                        'user_login_logs.created_at',
                        '>=',
                        Filters::get(Auth::User()->id, 'userLogingLogs', 'created_from') . ' 00:00:00'
                    );
                }
            }
        }

        if ($request->get('created_to') != '') {
            $where[] = array(
                'user_login_logs.created_at',
                '<=',
                $request->get('created_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, 'userLogingLogs', 'created_to', $request->get('created_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'userLogingLogs', 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, 'userLogingLogs', 'created_to')) {
                    $where[] = array(
                        'user_login_logs.created_at',
                        '<=',
                        Filters::get(Auth::User()->id, 'userLogingLogs', 'created_to') . ' 23:59:59'
                    );
                }
            }
        }
        return $where;
    }
}
