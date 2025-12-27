<?php

namespace App\Models;

use App\Helpers\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;

class DiscountAllocation extends Model
{
    use SoftDeletes;

    protected $fillable = ['discount_id', 'user_id', 'year', 'active', 'account_id', 'created_at', 'updated_at', 'deleted_at'];

    protected static $_fillable = ['discount_id', 'user_id', 'year', 'active', 'account_id'];

    protected $table = 'discountallocations';

    protected static $_table = 'discountallocations';

    /**
     * Get the user for the discount allocation.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Get the discount for the discount allocation.
     */
    public function discount()
    {
        return $this->belongsTo('App\Models\Discounts', 'discount_id');
    }

    /**
     * Get Total Records
     */
    static public function getTotalRecords(Request $request, $account_id = false, $apply_filter = false)
    {
        $where = self::discountallocations_filters($request, $account_id, $apply_filter);

        if (count($where)) {
            return self::where($where)->count();
        } else {
            return self::count();
        }
    }

    /**
     * Get Records
     */
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false, $apply_filter = false)
    {
        $where = self::discountallocations_filters($request, $account_id, $apply_filter);

        if (count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at', 'desc')->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at', 'desc')->get();
        }
    }

    /**
     * Get filters
     */
    static public function discountallocations_filters($request, $account_id, $apply_filter)
    {
        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::User()->id, 'discountallocations', 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'discountallocations', 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, 'discountallocations', 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::User()->id, 'discountallocations', 'account_id')
                    );
                }
            }
        }
        if ($request->get('discount_id')) {
            $where[] = array(
                'discount_id',
                '=',
                $request->get('discount_id')
            );
            Filters::put(Auth::User()->id, 'discountallocations', 'discount_id', $request->get('discount_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'discountallocations', 'discount_id');
            } else {
                if (Filters::get(Auth::User()->id, 'discountallocations', 'discount_id')) {
                    $where[] = array(
                        'discount_id',
                        '=',
                        Filters::get(Auth::User()->id, 'discountallocations', 'discount_id')
                    );
                }
            }
        }
        if ($request->get('user_id') != '') {
            $where[] = array(
                'user_id',
                '=',
                $request->get('user_id')
            );
            Filters::put(Auth::User()->id, 'discountallocations', 'user_id', $request->get('user_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'discountallocations', 'user_id');
            } else {
                if (Filters::get(Auth::User()->id, 'discountallocations', 'user_id')) {
                    $where[] = array(
                        'user_id',
                        '=',
                        Filters::get(Auth::User()->id, 'discountallocations', 'user_id')
                    );
                }
            }
        }
        if ($request->get('year') != '') {
            $where[] = array(
                'year',
                '=',
                $request->get('year')
            );
            Filters::put(Auth::User()->id, 'discountallocations', 'year', $request->get('year'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'discountallocations', 'year');
            } else {
                if (Filters::get(Auth::User()->id, 'discountallocations', 'year')) {
                    $where[] = array(
                        'year',
                        '=',
                        Filters::get(Auth::User()->id, 'discountallocations', 'year')
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
            Filters::put(Auth::User()->id, 'discountallocations', 'created_from', $request->get('created_from'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'discountallocations', 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, 'discountallocations', 'created_from')) {
                    $where[] = array(
                        'created_at',
                        '>=',
                        Filters::get(Auth::User()->id, 'discountallocations', 'created_from') . ' 00:00:00'
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
            Filters::put(Auth::User()->id, 'discountallocations', 'created_to', $request->get('created_to'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'discountallocations', 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, 'discountallocations', 'created_to')) {
                    $where[] = array(
                        'created_at',
                        '<=',
                        Filters::get(Auth::User()->id, 'discountallocations', 'created_to') . ' 23:59:59'
                    );
                }
            }
        }
        if ($request->get('status') && $request->get('status') != null || $request->get('status') == 0 && $request->get('status') != null) {
            $where[] = array(
                'active',
                '=',
                $request->get('status')
            );
            Filters::put(Auth::user()->id, 'discountallocations', 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'discountallocations', 'status');
            } else {
                if (Filters::get(Auth::user()->id, 'discountallocations', 'status') == 0 || Filters::get(Auth::user()->id, 'discountallocations', 'status') == 1) {
                    if (Filters::get(Auth::user()->id, 'discountallocations', 'status') != null) {
                        $where[] = array(
                            'active',
                            '=',
                            Filters::get(Auth::user()->id, 'discountallocations', 'status')
                        );
                    }
                }
            }
        }
        return $where;
    }

    /**
     * Create Record
     */
    static public function createRecord($request, $account_id)
    {

        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;
        $data['year'] = Carbon::now()->format('Y');

        $record = self::create($data);

        //log request for Create for Audit Trail

        AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);

        return $record;
    }

    /**
     * Update Record
     */
    static public function updateRecord($id, $request, $account_id)
    {
        $old_data = (DiscountAllocation::find($id))->toArray();

        $data = $request->all();
        // Set Account ID
        $data['account_id'] = $account_id;

        $record = self::where([
            'id' => $id,
            'account_id' => $account_id
        ])->first();

        if (!$record) {
            return null;
        }

        $record->update($data);

        AuditTrails::EditEventLogger(self::$_table, 'edit', $data, self::$_fillable, $old_data, $id);

        return $record;
    }

    /**
     * inactive Record
     */
    static public function inactiveRecord($id)
    {
        $discountallocation = DiscountAllocation::where([
            ['id', '=', $id],
            ['account_id', '=', Auth::User()->account_id]
        ])->first();

        if (!$discountallocation) {

            flash('Resource not found.')->error()->important();

            return redirect()->route('admin.discountallocations.index');

        }

        $record = $discountallocation->update(['active' => 0]);

        flash('Record has been inactivated successfully.')->success()->important();

        AuditTrails::InactiveEventLogger(self::$_table, 'inactive', self::$_fillable, $id);

        return $record;
    }

    /**
     * active Record
     */
    static function activeRecord($id)
    {
        $discountallocation = DiscountAllocation::where([
            ['id', '=', $id],
            ['account_id', '=', Auth::User()->account_id]
        ])->first();

        if (!$discountallocation) {

            flash('Resource not found.')->error()->important();

            return redirect()->route('admin.discountallocations.index');

        }

        $record = $discountallocation->update(['active' => 1]);

        flash('Record has been activated successfully.')->success()->important();

        AuditTrails::activeEventLogger(self::$_table, 'active', self::$_fillable, $id);

        return $record;
    }

    /**
     * Delete Record
     */
    static public function DeleteRecord($id)
    {
        $discountallocation = DiscountAllocation::where([
            ['id', '=', $id],
            ['account_id', '=', Auth::User()->account_id]
        ])->first();

        if (!$discountallocation) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.discountallocations.index');
        }

        if (DiscountAllocation::isChildExists($id, Auth::User()->account_id)) {

            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.discountallocations.index');
        }

        $record = $discountallocation->delete();

        //log request for delete for audit trail

        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

        return $record;
    }

    /**
     * Check if child records exist
     */
    static public function isChildExists($id, $account_id)
    {
        return false;
    }

    /*
     * Get Bulk Data
     */
    static public function getBulkData($id)
    {
        if (!is_array($id)) {
            $id = array($id);
        }
        return self::where([
            ['account_id', '=', Auth::User()->account_id]
        ])->whereIn('id', $id)->get();
    }
}
