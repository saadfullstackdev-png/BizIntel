<?php

namespace App\Models;

use App\Helpers\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Auth;

class Wallet extends Model
{
    protected $fillable = ['patient_id', 'account_id', 'created_at', 'updated_at'];
    protected $table = 'wallets';

    /**
     * get the user
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Patients', 'patient_id')->withTrashed();
    }
    
    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param boolean $apply_filter
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $apply_filter = false)
    {
        $where = Self::wallet_filters($request, $apply_filter);
        if (count($where)) {
            return self::where($where)->count();
        } else {
            return self::count();
        }
    }

    /**
     * Get Records
     * @param \Illuminate\Http\Request $request
     * @param integer $iDisplayStart
     * @param integer $iDisplayLength
     * @param boolean $apply_filter
     * @return (mixed)
     */
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $apply_filter = false)
    {
        $orderBy = 'created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        $where = Self::wallet_filters($request, $apply_filter);

        if (count($where)) {
            return self::where($where)
                ->limit($iDisplayLength)
                ->offset($iDisplayStart)
                ->orderBy($orderBy, $order)
                ->get();
        } else {
            return self::limit($iDisplayLength)
                ->offset($iDisplayStart)
                ->orderBy($orderBy, $order)
                ->get();
        }
    }

    /**
     * Get filters
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     * @param (boolean) $apply_filter
     * @return (mixed)
     */
    static public function wallet_filters($request, $apply_filter)
    {
        $where = array();

        if ($request->get('id') && $request->get('id') != '') {
            $where[] = array(
                'id',
                'like',
                '%' . $request->get('id') . '%'
            );
            Filters::put(Auth::User()->id, 'wallets', 'id', $request->get('id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'wallets', 'id');
            } else {
                if (Filters::get(Auth::User()->id, 'wallets', 'id')) {
                    $where[] = array(
                        'id',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'wallets', 'id') . '%'
                    );
                }
            }
        }

        if ($request->get('patient_id') && $request->get('patient_id') != '') {
            $where[] = array(
                'patient_id',
                '=',
                $request->get('patient_id')
            );
            Filters::put(Auth::user()->id, 'wallets', 'patient_id', $request->get('patient_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'wallets', 'patient_id');
            } else {
                if (Filters::get(Auth::user()->id, 'wallets', 'patient_id')) {
                    $where[] = array(
                        'patient_id',
                        '=',
                        Filters::get(Auth::user()->id, 'wallets', 'patient_id')
                    );
                }
            }
        }

        if ($request->get('created_from') && $request->get('created_from') != '') {
            $where[] = array(
                'created_at',
                '>=',
                $request->get('created_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, 'wallets', 'created_from', $request->get('created_from'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'wallets', 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, 'wallets', 'created_from')) {
                    $where[] = array(
                        'created_at',
                        '>=',
                        Filters::get(Auth::User()->id, 'wallets', 'created_from') . ' 00:00:00'
                    );
                }
            }
        }
        if ($request->get('created_to') && $request->get('created_to') != '') {
            $where[] = array(
                'created_at',
                '<=',
                $request->get('created_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, 'wallets', 'created_to', $request->get('created_to'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'wallets', 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, 'wallets', 'created_to')) {
                    $where[] = array(
                        'created_at',
                        '<=',
                        Filters::get(Auth::User()->id, 'wallets', 'created_to') . ' 23:59:59'
                    );
                }
            }
        }
        return $where;
    }

}
