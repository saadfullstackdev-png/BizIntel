<?php

namespace App\Models;

use App\Helpers\Filters;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Auth;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'payment_mode_id', 'order_id', 'amount', 'status', 'attempt', 'message', 'paid_for', 'paid_for_id', 'location_id'];


    public function payment_mode()
    {
        return $this->belongsTo(PaymentModes::class, 'payment_mode_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Locations::class, 'location_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundles::class, 'paid_for_id', 'id');
    }

    /**
     * Get Total Records
     */
    static public function getTotalRecords(Request $request, $apply_filter = false, $filename)
    {
        $where = self::filters($request, $apply_filter, $filename);

        if (count($where)) {
            return self::where($where)->count();
        } else {
            return self::count();
        }
    }

    /**
     * Get Records
     */
    static public function getRecords($request, $iDisplayStart, $iDisplayLength, $apply_filter = false, $filename)
    {
        $where = self::filters($request, $apply_filter, $filename);

        $orderBy = 'created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }
        if (count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderby($orderBy, $order)->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->orderby($orderBy, $order)->get();
        }
    }

    static public function filters($request, $apply_filter, $filename)
    {
        $where = array();

        if ($request->get('patient_id') && $request->get('patient_id') != '') {
            $where[] = array(
                'user_id',
                '=',
                $request->get('patient_id')
            );
            Filters::put(Auth::User()->id, $filename, 'patient_id', $request->get('patient_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'patient_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'patient_id')) {
                    $where[] = array(
                        'user_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'patient_id')
                    );
                }
            }
        }

        if ($request->get('payment_mode_id') && $request->get('payment_mode_id') != '') {
            $where[] = array(
                'payment_mode_id',
                '=',
                $request->get('payment_mode_id')
            );
            Filters::put(Auth::User()->id, $filename, 'payment_mode_id', $request->get('payment_mode_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'payment_mode_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'payment_mode_id')) {
                    $where[] = array(
                        'payment_mode_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'payment_mode_id')
                    );
                }
            }
        }

        if ($request->get('order_id') && $request->get('order_id')) {
            $where[] = array(
                'order_id',
                '=',
                $request->get('order_id')
            );
            Filters::put(Auth::User()->id, $filename, 'order_id', $request->get('order_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'order_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'order_id')) {
                    $where[] = array(
                        'order_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'order_id')
                    );
                }
            }
        }

        if ($request->get('amount') && $request->get('amount')) {
            $where[] = array(
                'amount',
                '=',
                $request->get('amount')
            );
            Filters::put(Auth::User()->id, $filename, 'amount', $request->get('amount'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'amount');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'amount')) {
                    $where[] = array(
                        'amount',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'amount')
                    );
                }
            }
        }

        if ($request->get('paid_for') && $request->get('paid_for')) {
            $where[] = array(
                'paid_for',
                '=',
                $request->get('paid_for')
            );
            Filters::put(Auth::User()->id, $filename, 'paid_for', $request->get('paid_for'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'paid_for');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'paid_for')) {
                    $where[] = array(
                        'paid_for',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'paid_for')
                    );
                }
            }
        }

        if ($request->get('status') && $request->get('status')) {
            $where[] = array(
                'status',
                '=',
                $request->get('status')
            );
            Filters::put(Auth::User()->id, $filename, 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'status');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'status')) {
                    $where[] = array(
                        'status',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'status')
                    );
                }
            }
        }

        if ($request->get('attempt') && $request->get('attempt')) {
            $where[] = array(
                'attempt',
                '=',
                $request->get('attempt')
            );
            Filters::put(Auth::User()->id, $filename, 'attempt', $request->get('attempt'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'attempt');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'attempt')) {
                    $where[] = array(
                        'attempt',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'attempt')
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
            Filters::put(Auth::User()->id, $filename, 'created_from', $request->get('created_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'created_from')) {
                    $where[] = array(
                        'created_at',
                        '>=',
                        Filters::get(Auth::User()->id, $filename, 'created_from') . ' 00:00:00'
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
            Filters::put(Auth::User()->id, $filename, 'created_to', $request->get('created_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'created_to')) {
                    $where[] = array(
                        'created_at',
                        '<=',
                        Filters::get(Auth::User()->id, $filename, 'created_to') . ' 23:59:59'
                    );
                }
            }
        }
        return $where;
    }

}
