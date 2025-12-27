<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Jobs\PromotionNotificationJob;
use App\Models\NotificationTemplates;
use App\Helpers\Filters;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;
use App\Helpers\GeneralFunctions;

class NotificationTemplatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('notification_templates_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'notification_templates');

        return view('admin.notification_templates.index', compact('filters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('notification_templates_create')) {
            return abort(401);
        }

        $notification_templates = new \stdClass();
        $notification_templates->image_url = null;
        $notification_templates->name = null;
        $notification_templates->content = null;
        $notification_templates->slug = null;

        return view('admin.notification_templates.create', compact('notification_templates'));
    }

    /**
     * Store a newly created notification_templates in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('notification_templates_create')) {
            return abort(401);
        }
        $notification_tempate_slug = NotificationTemplates::where('slug', '=', $request->slug)->first();
        if (!$notification_tempate_slug) {
            if (NotificationTemplates::createRecord($request, Auth::User()->account_id)) {
                flash('Record has been created successfully.')->success()->important();
                return redirect()->route('admin.notification_templates.index');
            } else {
                flash('Something went wrong, please try again later.')->warning()->important();
                return redirect()->route('admin.notification_templates.index');
            }
        } else {
            flash('Please provide unique slug')->warning()->important();
            return redirect()->route('admin.notification_templates.index');
        }

    }

    /**
     * Display a listing of banner.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;
        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'notification_templates');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $notification_templates = NotificationTemplates::getBulkData($request->get('id'));
            if ($notification_templates) {
                foreach ($notification_templates as $b) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!NotificationTemplates::isChildExists($b->id, Auth::User()->account_id)) {
                        $b->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = NotificationTemplates::getTotalRecords($request, Auth::User()->account_id, $apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $notification_templates = NotificationTemplates::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $apply_filter);

        if ($notification_templates) {
            foreach ($notification_templates as $noti_temp) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $noti_temp->id . '"/><span></span></label>',
                    'name' => $noti_temp->name,
                    'content' => substr($noti_temp->content, 0, 70) . '...',
                    'slug' => $noti_temp->slug,
                    'image_url' => view('admin.notification_templates.imageDisplay', compact('noti_temp'))->render(),
                    'status' => view('admin.notification_templates.status', compact('noti_temp'))->render(),
                    'actions' => view('admin.notification_templates.actions', compact('noti_temp'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('notification_templates_edit')) {
            return abort(401);
        }

        $notification_templates = NotificationTemplates::getData($id);

        if (!$notification_templates) {
            return view('error');
        }

        $creating = false;

        return view('admin.notification_templates.edit', compact('notification_templates', 'creating'));
    }

    /**
     * Update Permission in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('notification_templates_edit')) {
            return abort(401);
        }
        if (NotificationTemplates::updateRecord($id, $request, Auth::User()->account_id)) {
            flash('Record has been updated successfully.')->success()->important();
            return redirect()->route('admin.notification_templates.index');
        } else {
            flash('Something went wrong, please try again later.')->warning()->important();
            return redirect()->route('admin.notification_templates.index');
        }
    }

    /**
     * Remove notification_templates from table.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('notification_templates_destroy')) {
            return abort(401);
        }

        NotificationTemplates::DeleteRecord($id);

        return redirect()->route('admin.notification_templates.index');

    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('notification_templates_inactive')) {
            return abort(401);
        }
        NotificationTemplates::InactiveRecord($id);

        return redirect()->route('admin.notification_templates.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('notification_templates_active')) {
            return abort(401);
        }

        NotificationTemplates::activeRecord($id);

        return redirect()->route('admin.notification_templates.index');
    }

    public function sendPromoNotification($PromoId)
    {
        $notification_templates = NotificationTemplates::where(['id' => $PromoId, 'active' => 1])->first();

        if (!$notification_templates) {
            flash('Notification Templatet not active')->warning()->important();
            return redirect()->route('admin.notification_templates.index');
        }

        $users = User::where([
            ['active', '=', 1],
            ['is_mobile', '=', '1'],
            ['account_id', '=', Auth::User()->account_id],
            ['is_mobile_active', '=', 1],
            ['app_token', '!=', ""]
        ])->whereNotNull('app_token')->get();

        foreach ($users as $key => $user) {

            $promotion_job = (new PromotionNotificationJob([
                'account_id' => Auth::User()->account_id,
                'promo_id' => $PromoId,
                'token' => $user->app_token,
                'phone' => $user->phone ,
                'log_type' => 'promotion',
                'patient_id' => $user->id
            ]))->delay(Carbon::now()->addSeconds(2));

            dispatch($promotion_job);
        }

        flash('Notification has been Published Successfully.')->success()->important();

        return redirect()->route('admin.notification_templates.index');
    }
}
