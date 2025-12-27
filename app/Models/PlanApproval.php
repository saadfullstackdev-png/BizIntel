<?php

namespace App\Models;

use App\Helpers\ACL;
use App\Helpers\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;

class PlanApproval extends Model
{
    use SoftDeletes;

    protected $fillable = ['random_id', 'name', 'sessioncount', 'total_price', 'is_exclusive', 'account_id', 'patient_id', 'active', 'created_at', 'updated_at', 'deleted_at', 'location_id', 'appointment_id', 'bundle_id', 'package_selling_id', 'is_refund', 'is_hold', 'approved_by'];

    protected $table = 'packages';

    /*
     * get the data of patients from users table
     *
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'patient_id')->withTrashed();
    }

    /**
     * Get the packages.
     */
    public function packagesadvances()
    {

        return $this->hasMany('App\Models\PackageAdvances', 'package_id');
    }

    /*
    * get the data of location from location table
    *
    * */
    public function location()
    {
        return $this->belongsTo('App\Models\Locations', 'location_id')->withTrashed();
    }

    /*
     * get the data of appointment from package
     * */

    public function appointment()
    {
        $this->belongsTo(Appointments::class);
    }

    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $account_id = false, $id = false, $apply_filter = false, $filename, $discount_allocation_ids)
    {
        $where = self::filters($request, $account_id, $id, $apply_filter, $filename);

        if (count($where)) {
            return self::join('package_bundles', 'packages.id' ,'=', 'package_bundles.package_id')->where($where)->whereIn('package_bundles.discount_id', $discount_allocation_ids)->count();
        } else {
            return self::join('package_bundles', 'packages.id' ,'=', 'package_bundles.package_id')->whereIn('package_bundles.discount_id', $discount_allocation_ids)->count();
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
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false, $id = false, $apply_filter = false, $filename, $discount_allocation_ids)
    {
        $where = self::filters($request, $account_id, $id, $apply_filter, $filename);

        $orderBy = 'packages.created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }
        if (count($where)) {
            return self::join('package_bundles', 'packages.id' ,'=', 'package_bundles.package_id')->where($where)->whereIn('packages.location_id', ACL::getUserCentres())->whereIn('package_bundles.discount_id', $discount_allocation_ids)->limit($iDisplayLength)->offset($iDisplayStart)->orderby($orderBy, $order)->select('packages.*')->get();
        } else {
            return self::join('package_bundles', 'packages.id' ,'=', 'package_bundles.package_id')->whereIn('packages.location_id', ACL::getUserCentres())->whereIn('package_bundles.discount_id', $discount_allocation_ids)->limit($iDisplayLength)->offset($iDisplayStart)->orderby($orderBy, $order)->select('packages.*')->get();
        }
    }

    /*
     * Define filters
     */
    static public function filters($request, $account_id, $id = false, $apply_filter, $filename)
    {
        $where = array();

        $where[] = array(
            'packages.is_hold',
            '=',
            1
        );

        $where[] = array(
            'package_bundles.is_hold',
            '=',
            1
        );

        if ($id != false) {
            $where[] = array(
                'packages.patient_id',
                '=',
                $id
            );
            Filters::put(Auth::user()->id, $filename, 'patient_id', $id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, $filename, 'patient_id');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'patient_id')) {
                    $where[] = array(
                        'packages.patient_id',
                        '=',
                        Filters::get(Auth::user()->id, $filename, 'patient_id')
                    );
                }
            }
        }

        if ($account_id) {
            $where[] = array(
                'packages.account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::User()->id, $filename, 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'account_id')) {
                    $where[] = array(
                        'packages.account_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'account_id')
                    );
                }
            }
        }

        if ($request->get('patient_id') && $request->get('patient_id') != '') {
            $where[] = array(
                'packages.patient_id',
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
                        'packages.patient_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'patient_id')
                    );
                }
            }
        }

        if ($request->get('package_id') && $request->get('package_id') != '') {
            $where[] = array(
                'packages.id',
                '=',
                $request->get('package_id')
            );
            Filters::put(Auth::User()->id, $filename, 'package_id', $request->get('package_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'package_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'package_id')) {
                    $where[] = array(
                        'packages.id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'package_id')
                    );
                }
            }
        }
        if ($request->get('created_from') && $request->get('created_from') != '') {
            $where[] = array(
                'packages.created_at',
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
                        'packages.created_at',
                        '>=',
                        Filters::get(Auth::User()->id, $filename, 'created_from') . ' 00:00:00'
                    );
                }
            }
        }

        if ($request->get('created_to') && $request->get('created_to') != '') {
            $where[] = array(
                'packages.created_at',
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
                        'packages.created_at',
                        '<=',
                        Filters::get(Auth::User()->id, $filename, 'created_to') . ' 23:59:59'
                    );
                }
            }
        }

        if ($request->get('location_id') && $request->get('location_id')) {
            $where[] = array(
                'packages.location_id',
                '=',
                $request->get('location_id')
            );
            Filters::put(Auth::User()->id, $filename, 'location_id', $request->get('location_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'location_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'location_id')) {
                    $where[] = array(
                        'packages.location_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'location_id')
                    );
                }
            }
        }

        if ($request->get('package_selling_id') && $request->get('package_selling_id')) {
            $where[] = array(
                'packages.package_selling_id',
                '=',
                $request->get('package_selling_id')
            );
            Filters::put(Auth::User()->id, $filename, 'package_selling_id', $request->get('package_selling_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'package_selling_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'package_selling_id')) {
                    $where[] = array(
                        'packages.package_selling_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'package_selling_id')
                    );
                }
            }
        }

        if ($request->get('status') && $request->get('status') != null || $request->get('status') == 0 && $request->get('status') != null) {
            $where[] = array(
                'packages.active',
                '=',
                $request->get('status')
            );
            Filters::put(Auth::user()->id, $filename, 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, $filename, 'status');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'status') == 0 || Filters::get(Auth::user()->id, $filename, 'status') == 1) {
                    if (Filters::get(Auth::user()->id, $filename, 'status') != null) {
                        $where[] = array(
                            'packages.active',
                            '=',
                            Filters::get(Auth::user()->id, $filename, 'status')
                        );
                    }
                }
            }
        }

        return $where;
    }
}
