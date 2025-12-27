<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Models\Feedback;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;
use Carbon\Carbon;

class FeedBackController extends Controller
{

    /**
     * Display a listing of feedbacks.
     */
    public function index()
    {
        if (!Gate::allows('feedbacks_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'feedbacks');

        return view('admin.feedbacks.index', compact('filters'));
    }

    /**
     * Display a listing of feedbacks
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;
        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'feedbacks');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $feedbacks = Feedback::getBulkData($request->get('id'));
            if ($feedbacks) {
                foreach ($feedbacks as $Feedback) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!Feedback::isChildExists($Feedback->id, Auth::User()->account_id)) {
                        $Feedback->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Feedback::getTotalRecords($request, Auth::User()->account_id, $apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $feedbacks = Feedback::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $apply_filter);

        if ($feedbacks) {
            foreach ($feedbacks as $feedback) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $feedback->id . '"/><span></span></label>',
                    'user_id' => $feedback->usernameget->name,
                    'email' => $feedback->usernameget->email,
                    'phone' => $feedback->usernameget->phone,
                    'subject' => $feedback->subject,
                    'message' => $feedback->message,
                    'type' => $feedback->type,
                    'created_at' => Carbon::parse($feedback->created_at)->format('Y-m-d H:i:s'),
                    'actions' => view('admin.feedbacks.actions', compact('feedback'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Remove Permission from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('feedbacks_destroy')) {
            return abort(401);
        }
        Feedback::DeleteRecord($id);

        return redirect()->route('admin.feedbacks.index');

    }
}

