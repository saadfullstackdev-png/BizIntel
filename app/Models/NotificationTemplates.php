<?php

namespace App\Models;

use App\Helpers\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use App\Helpers\GeneralFunctions;


class NotificationTemplates extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['name', 'content', 'image_url', 'slug', 'active', 'is_promo', 'account_id', 'created_at', 'updated_at', 'deleted_at'];

    protected static $_fillable = ['name', 'content', 'image_url', 'slug', 'active', 'is_promo', 'account_id'];

    protected $table = 'notification_templates';

    protected static $_table = 'notification_templates';

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

        //Set Image
        if ($request->file('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $image_url = md5(time() . rand(0001, 9999) . rand(78599, 99999)) . ".$ext";
            $file->move('notification_templates_images', $image_url);
            $data['image_url'] = $image_url;
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
        $old_data = (NotificationTemplates::find($id))->toArray();

        $data = $request->all();
        // Set Account ID
        $data['account_id'] = $account_id;

        //Set Image
        if ($request->file('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $image_url = md5(time() . rand(0001, 9999) . rand(78599, 99999)) . ".$ext";
            $file->move('notification_templates_images', $image_url);
            $data['image_url'] = $image_url;
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

        $notification_templates = NotificationTemplates::getData($id);

        if (!$notification_templates) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.notification_templates.index');
        }

        $record = $notification_templates->update(['active' => 0]);

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
        $notification_templates = NotificationTemplates::getData($id);

        if (!$notification_templates) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.notification_templates.index');
        }
        $record = $notification_templates->update(['active' => 1]);
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
        $notification_templates = NotificationTemplates::getData($id);

        if (!$notification_templates) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.notification_templates.index');
        }
        // Check if child records exists or not, If exist then disallow to delete it.
        if (NotificationTemplates::isChildExists($id, Auth::User()->account_id)) {

            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.notification_templates.index');
        }

        $record = $notification_templates->delete();

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
        $where = Self::notification_templates_filters($request, $account_id, $apply_filter);

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
        $where = Self::notification_templates_filters($request, $account_id, $apply_filter);

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
    static public function notification_templates_filters($request, $account_id, $apply_filter)
    {
        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::User()->id, 'notification_templates', 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'notification_templates', 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, 'notification_templates', 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::User()->id, 'notification_templates', 'account_id')
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
            Filters::put(Auth::User()->id, 'notification_templates', 'name', $request->get('title'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'notification_templates', 'name');
            } else {
                if (Filters::get(Auth::User()->id, 'notification_templates', 'name')) {
                    $where[] = array(
                        'name',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'notification_templates', 'name') . '%'
                    );
                }
            }
        }

        if ($request->get('content')) {
            $where[] = array(
                'content',
                'like',
                '%' . $request->get('content') . '%'
            );
            Filters::put(Auth::User()->id, 'notification_templates', 'content', $request->get('body'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'notification_templates', 'content');
            } else {
                if (Filters::get(Auth::User()->id, 'notification_templates', 'content')) {
                    $where[] = array(
                        'content',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'notification_templates', 'content') . '%'
                    );
                }
            }
        }

        if ($request->get('slug')) {
            $where[] = array(
                'slug',
                'like',
                '%' . $request->get('slug') . '%'
            );
            Filters::put(Auth::User()->id, 'notification_templates', 'body', $request->get('slug'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'notification_templates', 'slug');
            } else {
                if (Filters::get(Auth::User()->id, 'notification_templates', 'slug')) {
                    $where[] = array(
                        'slug',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'notification_templates', 'slug') . '%'
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
            Filters::put(Auth::user()->id, 'notification_templates', 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'notification_templates', 'status');
            } else {
                if (Filters::get(Auth::user()->id, 'notification_templates', 'status') == 0 || Filters::get(Auth::user()->id, 'notification_templates', 'status') == 1) {
                    if (Filters::get(Auth::user()->id, 'notification_templates', 'status') != null) {
                        $where[] = array(
                            'active',
                            '=',
                            Filters::get(Auth::user()->id, 'notification_templates', 'status')
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
    static public function getBySlug($slug, $account_id)
    {
        return self::where(['slug' => $slug, 'account_id' => $account_id, 'active' => 1])->first();
    }
    
}
