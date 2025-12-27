<?php

namespace App\Models;

use App\Helpers\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;

class Banner extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['image_src', 'account_id', 'active', 'created_at', 'updated_at', 'deleted_at', 'banner_type', 'banner_value'];

    protected static $_fillable = ['image_src', 'banner_type', 'banner_value', 'account_id', 'active'];

    protected $table = 'banners';

    protected static $_table = 'banners';

    /**
     * Create Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function createRecord($request, $account_id)
    {
        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;
        $data['banner_type'] = $request->banner_type;
        $data['banner_value'] = $request->banner_value;

        //dd($data);


        //Set Image
        if ($request->file('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $image_url = md5(time() . rand(0001, 9999) . rand(78599, 99999)) . ".$ext";
            $file->move('banners_images', $image_url);
            $data['image_src'] = $image_url;
        }

        $record = self::create($data);
        //log request for Create for Audit Trail
        AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);
        return $record;
    }

    /**
     * Update Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function updateRecord($id, $request, $account_id)
    {
        $old_data = (Banner::find($id))->toArray();

        $data = $request->all();
        // Set Account ID
        $data['account_id'] = $account_id;

        //Set Image
        if ($request->file('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $image_url = md5(time() . rand(0001, 9999) . rand(78599, 99999)) . ".$ext";
            $file->move('banners_images', $image_url);
            $data['image_src'] = $image_url;
        }
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
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function inactiveRecord($id)
    {

        $banner = Banner::getData($id);

        if (!$banner) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.banner.index');
        }

        $record = $banner->update(['active' => 0]);

        flash('Record has been inactivated successfully.')->success()->important();

        AuditTrails::InactiveEventLogger(self::$_table, 'inactive', self::$_fillable, $id);

        return $record;
    }

    /**
     * active Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static function activeRecord($id)
    {
        $banner = Banner::getData($id);

        if (!$banner) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.banner.index');
        }
        $record = $banner->update(['active' => 1]);
        flash('Record has been activated successfully.')->success()->important();
        AuditTrails::activeEventLogger(self::$_table, 'active', self::$_fillable, $id);

        return $record;
    }

    /**
     * Delete Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function DeleteRecord($id)
    {
        $banner = Banner::getData($id);

        if (!$banner) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.banner.index');
        }
        // Check if child records exists or not, If exist then disallow to delete it.
        if (Banner::isChildExists($id, Auth::User()->account_id)) {

            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.banner.index');
        }

        $record = $banner->delete();

        //log request for delete for audit trail
        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

        return $record;
    }

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
        $where = Self::banners_filters($request, $account_id, $apply_filter);

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
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false, $apply_filter = false)
    {
        $where = Self::banners_filters($request, $account_id, $apply_filter);

        if (count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at')->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at')->get();
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
    static public function banners_filters($request, $account_id, $apply_filter)
    {
        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::User()->id, 'banners', 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'banners', 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, 'banners', 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::User()->id, 'banners', 'account_id')
                    );
                }
            }
        }

        if ($request->get('banner_type')) {
            $where[] = array(
                'banner_type',
                'like',
                '%' . $request->get('banner_type') . '%'
            );
            Filters::put(Auth::User()->id, 'banners', 'banner_type', $request->get('banner_type'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'banners', 'banner_type');
            } else {
                if (Filters::get(Auth::User()->id, 'banners', 'banner_type')) {
                    $where[] = array(
                        'banner_type',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'banners', 'banner_type') . '%'
                    );
                }
            }
        }

        if ($request->get('banner_value')) {
            $where[] = array(
                'banner_value',
                'like',
                '%' . $request->get('banner_value') . '%'
            );
            Filters::put(Auth::User()->id, 'banners', 'banner_value', $request->get('banner_value'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'banners', 'banner_value');
            } else {
                if (Filters::get(Auth::User()->id, 'banners', 'banner_value')) {
                    $where[] = array(
                        'banner_value',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'banners', 'banner_value') . '%'
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
            Filters::put(Auth::user()->id, 'banners', 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'banners', 'status');
            } else {
                if (Filters::get(Auth::user()->id, 'banners', 'status') == 0 || Filters::get(Auth::user()->id, 'banners', 'status') == 1) {
                    if (Filters::get(Auth::user()->id, 'banners', 'status') != null) {
                        $where[] = array(
                            'active',
                            '=',
                            Filters::get(Auth::user()->id, 'banners', 'status')
                        );
                    }
                }
            }
        }

        return $where;
    }

    /**
     * Check if child records exist
     *
     * @param (int) $id
     * @param
     *
     * @return (boolean)
     */
    static public function isChildExists($id, $account_id)
    {
        return false;
    }
}
