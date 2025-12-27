<?php

namespace App\Models;

use App\Helpers\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;

class Category extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['name', 'account_id', 'active', 'created_at', 'updated_at'];

    protected static $_fillable = ['name', 'account_id', 'active'];

    protected $table = 'categories';

    protected static $_table = 'categories';

    /**
     * Get Total Records
     */
    static public function getTotalRecords(Request $request, $account_id = false, $apply_filter = false)
    {
        $where = Self::categories_filters($request, $account_id, $apply_filter);

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
        $where = Self::categories_filters($request, $account_id, $apply_filter);

        if (count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at')->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at')->get();
        }
    }

    /**
     * Get filters
     */
    static public function categories_filters($request, $account_id, $apply_filter)
    {

        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::User()->id, 'categories', 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'categories', 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, 'categories', 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::User()->id, 'categories', 'account_id')
                    );
                }
            }
        }
        if ($request->get('name')) {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
            );
            Filters::put(Auth::User()->id, 'categories', 'name', $request->get('name'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'categories', 'name');
            } else {
                if (Filters::get(Auth::User()->id, 'categories', 'name')) {
                    $where[] = array(
                        'name',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'categories', 'name') . '%'
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
            Filters::put(Auth::user()->id, 'categories', 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'categories', 'status');
            } else {
                if (Filters::get(Auth::user()->id, 'categories', 'status') == 0 || Filters::get(Auth::user()->id, 'cities', 'status') == 1) {
                    if (Filters::get(Auth::user()->id, 'categories', 'status') != null) {
                        $where[] = array(
                            'active',
                            '=',
                            Filters::get(Auth::user()->id, 'categories', 'status')
                        );
                    }
                }
            }
        }
        return $where;
    }

    /**
     * Check if child records exist
     */
    static public function isChildExists($id, $account_id)
    {
//        if (
//            Faqs::where(['category_id' => $id, 'account_id' => $account_id])->count()
//        ) {
//            return true;
//        }

        return false;
    }

    /**
     * Create Record
     */
    static public function createRecord($request, $account_id)
    {

        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;

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
        $old_data = (Category::find($id))->toArray();

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
     * Delete Record
     */
    static public function DeleteRecord($id)
    {
        $category = Category::getData($id);

        if (!$category) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.categories.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (Category::isChildExists($id, Auth::User()->account_id)) {

            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.categories.index');
        }

        $record = $category->delete();

        //log request for delete for audit trail

        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

        return $record;
    }

    /**
     * inactive Record
     */
    static public function inactiveRecord($id)
    {
        $category = Category::getData($id);

        if (!$category) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.categories.index');
        }

        $record = $category->update(['active' => 0]);

        flash('Record has been inactivated successfully.')->success()->important();

        AuditTrails::InactiveEventLogger(self::$_table, 'inactive', self::$_fillable, $id);

        return $record;
    }

    /**
     * active Record
     */
    static function activeRecord($id)
    {
        $category = Category::getData($id);

        if (!$category) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.categories.index');
        }

        $record = $category->update(['active' => 1]);

        flash('Record has been activated successfully.')->success()->important();

        AuditTrails::activeEventLogger(self::$_table, 'active', self::$_fillable, $id);

        return $record;
    }
    public function subscriptionCharges()
    {
        return $this->belongsToMany(
            SubscriptionCharge::class,
            'category_subscription_charge',
            'category_id',
            'subscription_charge_id'
        )->withPivot('offered_discount')
        ->withTimestamps();
    }


}
