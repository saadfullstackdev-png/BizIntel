<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Filters;
use App\Models\BundleHasServices;
use App\Models\Bundles;
use App\Models\ContentDisplayType;
use App\Models\Services;
use App\Models\TaxTreatmentType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;

class BundlesController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('packages_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'bundles');

        $display_content_types = ContentDisplayType::get()->pluck('name', 'id');
        $display_content_types->prepend('Select a Content Type', '');

        return view('admin.bundles.index', compact('filters', 'display_content_types'));
    }

    /**
     * Display a listing of Lead_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;
        if($request->get('action')) {
            $action = $request->get('action');
            if(isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'bundles');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $Bundles = Bundles::getBulkData($request->get('id'));
            if($Bundles) {
                foreach($Bundles as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!Bundles::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Bundles::getTotalRecords($request, Auth::User()->account_id , $apply_filter);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $Bundles = Bundles::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $apply_filter);

        if($Bundles) {
            foreach($Bundles as $bundle) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$bundle->id.'"/><span></span></label>',
                    'name' => $bundle->name,
                    'price' => number_format($bundle->price, 2),
                    'total_services' => $bundle->total_services,
                    'apply_discount' => ($bundle->apply_discount) ? 'Yes' : 'No',
                    'is_mobile' => $bundle->contentdisplaytype->name,
                    'created_at' => Carbon::parse($bundle->created_at)->format('F j,Y h:i A'),
                    'status' => view('admin.bundles.status', compact('bundle'))->render(),
                    'actions' => view('admin.bundles.actions', compact('bundle'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('packages_create')) {
            return abort(401);
        }

        $services = Services::getServicesForBundle();

        $tax_treatment_types = TaxTreatmentType::get();

        $content_display_types = ContentDisplayType::get();

        return view('admin.bundles.create',compact('services','tax_treatment_types', 'content_display_types'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('packages_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(Bundles::createRecord($request, Auth::User()->account_id)) {
            flash('Record has been created successfully.')->success()->important();
            return redirect()->route('admin.bundles.index');
        } else {
            flash('Something went wrong, please try again later.')->warning()->important();
            return redirect()->route('admin.bundles.index');
        }
    }

    /**
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'total_services' => 'required|numeric|min:1',
            'service_id' => 'required|array',
            'tax_treatment_type_id' => 'required',
            'is_mobile' => 'required'
        ]);
    }

    /**
     * Show the form for editing Permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('packages_edit')) {
            return abort(401);
        }

        $bundle = Bundles::getData($id);

        if(!$bundle) {
            return view('error', compact('lead_statuse'));
        }

        $services = Services::getServicesForBundle();

        $relation_service_ids = BundleHasServices::where(array(
            'bundle_id' => $bundle->id
        ))->select('service_id')->get();

        $relationships = BundleHasServices::where(array(
            'bundle_id' => $bundle->id
        ))->select('service_id', 'service_price')->get();

        $bundle_services = collect(new Services());

        if($relationships->count()) {
            $bundle_services = Services::whereIn('id', $relation_service_ids)->where(['account_id' => Auth::User()->account_id])->get()->getDictionary();
        }

        $tax_treatment_types = TaxTreatmentType::get();

        $content_display_types = ContentDisplayType::get();

        return view('admin.bundles.edit', compact('bundle', 'services', 'bundle_services', 'relationships','tax_treatment_types', 'content_display_types'));
    }

    /**
     * Update Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('packages_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(Bundles::updateRecord($id, $request, Auth::User()->account_id)) {
            flash('Record has been updated successfully.')->success()->important();
            return redirect()->route('admin.bundles.index');
        } else {
            flash('Something went wrong, please try again later.')->warning()->important();
            return redirect()->route('admin.bundles.index');
        }
    }

    /**
     * Show Lead detail.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        if (!Gate::allows('packages_manage')) {
            return abort(401);
        }
        $bundle = Bundles::findOrFail($id);

        if(!$bundle) {
            return view('error', compact('lead_statuse'));
        }

        $relation_service_ids = BundleHasServices::where(array(
            'bundle_id' => $bundle->id
        ))->select('service_id')->get();

        $relationships = BundleHasServices::where(array(
            'bundle_id' => $bundle->id
        ))->select('service_id', 'service_price')->get();

        $bundle_services = array();
        $bundle_services = collect(new Services());

        if($relationships->count()) {
            $bundle_services = Services::whereIn('id', $relation_service_ids)->where(['account_id' => Auth::User()->account_id])->get()->getDictionary();
        }

        return view('admin.bundles.detail', compact('bundle', 'bundle_services', 'relationships'));
    }

    /**
     * Remove Permission from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('packages_destroy')) {
            return abort(401);
        }

        Bundles::DeleteRecord($id);

        return redirect()->route('admin.bundles.index');

    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('packages_inactive')) {
            return abort(401);
        }
        Bundles::InactiveRecord($id);

        return redirect()->route('admin.bundles.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('packages_active')) {
            return abort(401);
        }

        Bundles::activeRecord($id);
        
        return redirect()->route('admin.bundles.index');
    }
}
