<?php

namespace App\Models;

use App\Helpers\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;

class PackageSelling extends Model
{
    use SoftDeletes;

    protected $fillable = ['bundle_id', 'patient_id', 'location_id', 'name', 'actual_price', 'offered_price', 'total_services', 'apply_discount', 'is_exclusive', 'tax_exclusive_price', 'tax_percentage', 'tax_price', 'tax_including_price', 'created_at', 'updated_at', 'deleted_at'];

    protected static $_fillable = ['bundle_id', 'patient_id', 'location_id', 'name', 'actual_price', 'offered_price', 'total_services', 'apply_discount', 'is_exclusive', 'tax_exclusive_price', 'tax_percentage', 'tax_price', 'tax_including_price'];

    protected $table = 'package_sellings';

    protected static $_table = 'package_sellings';

    /**
     * get the user
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Patients', 'patient_id')->withTrashed();
    }

    /*
     * get the data of location from location table
     *
     * */
    public function location()
    {
        return $this->belongsTo('App\Models\Locations', 'location_id')->withTrashed();
    }

    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $apply_filter = false)
    {
        $where = Self::packageSelling_filters($request, $apply_filter);
        if (count($where)) {
            return self::where($where)->count();
        } else {
            return self::count();
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
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $apply_filter = false)
    {
        $orderBy = 'created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        $where = Self::packageSelling_filters($request, $apply_filter);

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
    static public function packageSelling_filters($request, $apply_filter)
    {
        $where = array();

        if ($request->get('id') && $request->get('id') != '') {
            $where[] = array(
                'id',
                'like',
                '%' . $request->get('id') . '%'
            );
            Filters::put(Auth::User()->id, 'packagesellings', 'id', $request->get('id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'packagesellings', 'id');
            } else {
                if (Filters::get(Auth::User()->id, 'packagesellings', 'id')) {
                    $where[] = array(
                        'id',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'packagesellings', 'id') . '%'
                    );
                }
            }
        }

        if ($request->get('name') && $request->get('name') != '') {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
            );
            Filters::put(Auth::User()->id, 'packagesellings', 'name', $request->get('name'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'packagesellings', 'name');
            } else {
                if (Filters::get(Auth::User()->id, 'packagesellings', 'name')) {
                    $where[] = array(
                        'name',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'packagesellings', 'name') . '%'
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
            Filters::put(Auth::user()->id, 'packagesellings', 'patient_id', $request->get('patient_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'packagesellings', 'patient_id');
            } else {
                if (Filters::get(Auth::user()->id, 'packagesellings', 'patient_id')) {
                    $where[] = array(
                        'patient_id',
                        '=',
                        Filters::get(Auth::user()->id, 'packagesellings', 'patient_id')
                    );
                }
            }
        }
        if ($request->get('total_services') && $request->get('total_services') != '') {
            $where[] = array(
                'total_services',
                'like',
                '%' . $request->get('total_services') . '%'
            );
            Filters::put(Auth::User()->id, 'packagesellings', 'total_services', $request->get('total_services'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'packagesellings', 'total_services');
            } else {
                if (Filters::get(Auth::User()->id, 'packagesellings', 'total_services')) {
                    $where[] = array(
                        'total_services',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'packagesellings', 'total_services') . '%'
                    );
                }
            }
        }
        if ($request->get('tax_including_price') && $request->get('tax_including_price') != '') {
            $where[] = array(
                'tax_including_price',
                'like',
                '%' . $request->get('tax_including_price') . '%'
            );
            Filters::put(Auth::User()->id, 'packagesellings', 'tax_including_price', $request->get('tax_including_price'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'packagesellings', 'tax_including_price');
            } else {
                if (Filters::get(Auth::User()->id, 'packagesellings', 'tax_including_price')) {
                    $where[] = array(
                        'tax_including_price',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'packagesellings', 'tax_including_price') . '%'
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
            Filters::put(Auth::User()->id, 'packagesellings', 'created_from', $request->get('created_from'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'packagesellings', 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, 'packagesellings', 'created_from')) {
                    $where[] = array(
                        'created_at',
                        '>=',
                        Filters::get(Auth::User()->id, 'packagesellings', 'created_from') . ' 00:00:00'
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
            Filters::put(Auth::User()->id, 'packagesellings', 'created_to', $request->get('created_to'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'packagesellings', 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, 'packagesellings', 'created_to')) {
                    $where[] = array(
                        'created_at',
                        '<=',
                        Filters::get(Auth::User()->id, 'packagesellings', 'created_to') . ' 23:59:59'
                    );
                }
            }
        }
        return $where;
    }
}
