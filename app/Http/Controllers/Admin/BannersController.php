<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Models\Banner;
use App\Models\Bundles;
use App\Models\Services;
use App\Helpers\Filters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;

class BannersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('banners_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'banners');

        return view('admin.banner.index', compact('filters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('banners_create')) {
            return abort(401);
        }

        $banner = new \stdClass();
        $banner->image_src = null;

        return view('admin.banner.create', compact('banner'));
    }
    /**
     * Validate form fields
     *
     * @param \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyUpdateFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'banner_type' => 'required',
            'banner_value' => 'required',
            'file' => 'required'
        ]);
    }
    /**
     * Store a newly created banner in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('banners_create')) {
            return abort(401);
        }
         $validator = $this->verifyUpdateFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
                'id' => 0,
            ));
        }

        if (Banner::createRecord($request, Auth::User()->account_id)) {
            flash('Record has been created successfully.')->success()->important();
            return redirect()->route('admin.banner.index');
        } else {
            flash('Something went wrong, please try again later.')->warning()->important();
            return redirect()->route('admin.banner.index');
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
                Filters::flush(Auth::User()->id, 'banners');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $banner = Banner::getBulkData($request->get('id'));
            if ($banner) {
                foreach ($banner as $b) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!Banner::isChildExists($b->id, Auth::User()->account_id)) {
                        $b->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Banner::getTotalRecords($request, Auth::User()->account_id, $apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $banners = Banner::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $apply_filter);

        if ($banners) {
            foreach ($banners as $banner) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $banner->id . '"/><span></span></label>',
                    'image' => view('admin.banner.imageDisplay', compact('banner'))->render(),
                    'banner_type' => $banner->banner_type,
                    'banner_value' => $banner->banner_value,
                    'status' => view('admin.banner.status', compact('banner'))->render(),
                    'actions' => view('admin.banner.actions', compact('banner'))->render(),
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
        if (!Gate::allows('banners_edit')) {
            return abort(401);
        }

        $banner = Banner::getData($id);

        if (!$banner) {
            return view('error');
        }

        return view('admin.banner.edit', compact('banner'));
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
        if (!Gate::allows('banners_edit')) {
            return abort(401);
        }
        if (Banner::updateRecord($id, $request, Auth::User()->account_id)) {
            flash('Record has been updated successfully.')->success()->important();
            return redirect()->route('admin.banner.index');
        } else {
            flash('Something went wrong, please try again later.')->warning()->important();
            return redirect()->route('admin.banner.index');
        }
    }

    /**
     * Remove banner from table.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('banners_destroy')) {
            return abort(401);
        }

        Banner::DeleteRecord($id);

        return redirect()->route('admin.banner.index');

    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('banners_inactive')) {
            return abort(401);
        }
        Banner::InactiveRecord($id);

        return redirect()->route('admin.banner.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('banners_active')) {
            return abort(401);
        }

        Banner::activeRecord($id);

        return redirect()->route('admin.banner.index');
    }


    public function get_banner_services()
    {
//        dd('i am here');
        try {
            $services = Services::where([
                ['active', 1],
                ['is_mobile', '=', 1]
            ])->where('account_id', 1)->get();

            if (count($services) > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Services Found.',
                    'services' => $services,
                    'status_code' => 200,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Found.',
                    'services' => [],
                    'status_code' => 204,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => 402,
            ]);
        }
    }

    public function get_banner_bundles()
    {
//        dd('i am here');
        try {
            $bundles = Bundles::where([
                ['active', 1],
                ['is_mobile', '!=', 3]
            ])->where('account_id', 1)->get();

            if (count($bundles) > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Bundles Found.',
                    'bundles' => $bundles,
                    'status_code' => 200,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Found.',
                    'bundles' => [],
                    'status_code' => 204,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => 402,
            ]);
        }
    }


}
