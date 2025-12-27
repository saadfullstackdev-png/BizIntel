<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\NodesTree;
use App\Helpers\Widgets\LocationsWidget;
use App\Models\Cities;
use App\Models\Locations;
use App\Models\Regions;
use App\Models\ServiceHasLocations;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;
use Carbon\Carbon;
use App\User;
use App\Models\UserHasLocations;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\ACL;
use App\Helpers\GeneralFunctions;
use App\Helpers\Filters;

class LocationsController extends Controller
{
    /**
     * Display a listing of Location.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('locations_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'locations');

        $cities = Cities::where([
            ['account_id', '=', Auth::User()->account_id],
            ['slug', '=', 'custom'],
            ['active', '=', '1'],
            ['is_featured', '=', '1']
        ])->get()->pluck('name', 'id');
        $cities->prepend('Select a City', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());
        $regions->prepend('Select a Region', '');

        /* Create Nodes with Parents */
        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);

        $Services = $parentGroups->nodeList;

        return view('admin.locations.index', compact('cities', 'Services', 'regions', 'filters'));
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
        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'locations');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $Locations = Locations::getBulkData($request->get('id'));
            if ($Locations) {
                foreach ($Locations as $Location) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!Locations::isChildExists($Location->id, Auth::User()->account_id)) {
                        $Location->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Locations::getTotalRecords($request, Auth::User()->account_id, $apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $Locations = Locations::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $apply_filter);

        $Services = Services::getAllRecordsDictionary(Auth::User()->account_id);
        $Cities = Cities::getAllRecordsDictionary(Auth::User()->account_id);
        $Regions = Regions::getAllRecordsDictionary(Auth::User()->account_id);

        if ($Locations) {
            foreach ($Locations as $location) {

                $city = Cities::getData($location->id);

                /*
                 * Record Level Services process start
                 */
                $_services = '';

                $locationServices = ServiceHasLocations::where(['location_id' => $location->id])->get()->pluck('service_id');
                if (!$locationServices->isEmpty() && count($locationServices)) {
                    foreach ($locationServices as $_location) {
                        if (array_key_exists($_location, $Services)) {
                            $_services .= '<span class="label label-sm label-info">' . $Services[$_location]->name . '</span>&nbsp;';
                        }
                    }
                }
                /*
                 * Record Level Services process end
                 */

                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $location->id . '"/><span></span></label>',
                    'name' => $location->name,
                    'fdo_name' => $location->fdo_name ? $location->fdo_name : 'N/A',
                    'fdo_phone' => $location->fdo_phone ? GeneralFunctions::prepareNumber4Call($location->fdo_phone) : 'N/A',
                    'address' => $location->address,
                    'city' => (array_key_exists($location->city_id, $Cities)) ? $Cities[$location->city_id]->name : 'N/A',
                    'region' => (array_key_exists($location->region_id, $Regions)) ? $Regions[$location->region_id]->name : 'N/A',
                    'service' => ($_services) ? $_services : 'N/A',
                    'created_at' => Carbon::parse($location->created_at)->format('F j,Y h:i A'),
                    'status' => view('admin.locations.status', compact('location'))->render() ,
                    'actions' => view('admin.locations.actions', compact('location'))->render(),
                );

            }
        }


        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating new Location.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('locations_create')) {
            return abort(401);
        }

        /* Create Nodes with Parents */
        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id, true, true);
        $parentGroups->toList($parentGroups, -1);

        $Services = $parentGroups->nodeList;

        $cities = Cities::where([
            ['account_id', '=', Auth::User()->account_id],
            ['slug', '=', 'custom'],
            ['active', '=', '1'],
            ['is_featured', '=', '1']
        ])->get()->pluck('full_name', 'id');
        $cities->prepend('Select a City', '');

        $ServiceLocations = array();

        return view('admin.locations.create', compact('cities', 'Services', 'ServiceLocations'));
    }

    /**
     * Store a newly created Location in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('locations_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);
        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }
        if ($location = Locations::createRecord($request, Auth::User()->account_id)) {

            $locatUser = array();

            $location_slug_all = Locations::where('slug', '=', 'all')->first();

            $user_has_location_data = UserHasLocations::where('location_id', '=', $location_slug_all->id)->groupby('user_id')->get();

            if (count($user_has_location_data) > 0) {
                foreach ($user_has_location_data as $user) {

                    $user_has_locations = array(
                        'user_id' => $user->user_id,
                        'region_id' => $location->region_id,
                        'location_id' => $location->id,
                    );
                    // Insert assigned centres to User
                    UserHasLocations::createRecord($user_has_locations, $user->user_id);
                }
            }
            $user_already_have = UserHasLocations::where('location_id', '=', $location->id)->select('user_id')->groupby('user_id')->get();
            foreach ($user_already_have as $users) {
                $user_already_have_location[] = $users->user_id;
            }
            $head_region = Locations::where([
                ['slug', '=', 'region'],
                ['region_id', '=', $location->region_id]
            ])->first();
            $user_has_location_data = UserHasLocations::where([
                ['location_id', '=', $head_region->id],
                ['location_id', '!=', $location->id],
            ])->select('user_id')->groupby('user_id')->get();

            foreach ($user_has_location_data as $Need_to_lcoateuser) {
                if (!in_array($Need_to_lcoateuser->user_id, $user_already_have_location)) {
                    $locatUser[] = $Need_to_lcoateuser->user_id;
                }
            }
            if (count($locatUser) > 0) {
                foreach ($locatUser as $user) {
                    $user_has_locations = array(
                        'user_id' => $user,
                        'region_id' => $location->region_id,
                        'location_id' => $location->id,
                    );
                    // Insert assigned centres to User
                    UserHasLocations::createRecord($user_has_locations, $user);
                }
            }
            /*
             * Prepare services data for location
             */
            $data = $request->all();
            /*
             * New Audit Trail Process
             */
            if (isset($data['services']) && count($data['services'])) {
                $services = LocationsWidget::generateservicearray($data['services'], session('account_id'));
                $servicesData = array();
                foreach ($services as $service) {
                    $servicesData = array(
                        'service_id' => $service,
                        'location_id' => $location->id,
                        'account_id' => Auth::User()->account_id,
                    );
                    ServiceHasLocations::createRecord($servicesData, $location);
                }
            }

            flash('Record has been created successfully.')->success()->important();

            return redirect()->route('admin.locations.index');
        } else {

            flash('Something went wrong, please try again later.')->success()->important();

            return redirect()->route('admin.locations.index');
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
            'fdo_name' => 'required',
            'fdo_phone' => 'required',
            'address' => 'required',
            'google_map' => 'required',
            'city_id' => 'required',
            'ntn' => 'required',
            'stn' => 'required',
            /*'ntn' => ['required', 'regex:/^([0-9]|\.|\+|\*|\-|\_|\#)*$/'],
            'stn' => ['required', 'regex:/^([0-9]|\.|\+|\*|\-|\_|\#)*$/'],*/
        ]);
    }



    /**
     * Show the form for editing Location.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('locations_edit')) {
            return abort(401);
        }

        $location = Locations::getData($id);

        if (!$location) {
            return view('error', compact('lead_statuse'));
        }

        $ServiceLocations = $location->service_has_locations()->pluck('service_id')->toArray();

        $cities = Cities::where([
            ['account_id', '=', Auth::User()->account_id],
            ['slug', '=', 'custom'],
            ['active', '=', '1'],
            ['is_featured', '=', '1']
        ])->get()->pluck('full_name', 'id');
        $cities->prepend('Select a City', '');

        /* Create Nodes with Parents */
        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id, true, true);
        $parentGroups->toList($parentGroups, -1);

        $Services = $parentGroups->nodeList;

        return view('admin.locations.edit', compact('location', 'cities', 'Services', 'ServiceLocations'));
    }

    /**
     * Update Location in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('locations_edit')) {
            return abort(401);
        }
        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }
        if ($location = Locations::updateRecord($id, $request, Auth::User()->account_id)) {

            $location->service_has_locations()->delete();

            $data = $request->all();

//            $deletestatus = LocationsWidget::checkedLocationHasServiceEditArray($data['services'],session('account_id'),$location);

            /*
             * Prepare services data for location
             */

            if (isset($data['services']) && count($data['services'])) {
                $servicesData = array();
                $services = LocationsWidget::generateservicearray($data['services'], session('account_id'));
                foreach ($services as $service) {
                    $servicesData = array(
                        'service_id' => $service,
                        'location_id' => $location->id,
                        'account_id' => Auth::User()->account_id,
                    );
                    ServiceHasLocations::updateRecord($servicesData, $location);
                }
            }

            flash('Record has been updated successfully.')->success()->important();

            return redirect()->route('admin.locations.index');
        } else {

            flash('Something went wrong, please try again later.')->success()->important();

            return redirect()->route('admin.locations.index');
        }
    }

    /**
     * Remove Location from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('locations_destroy')) {
            return abort(401);
        }
        Locations::deleteRecord($id);

        return redirect()->route('admin.locations.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('locations_inactive')) {
            return abort(401);
        }

        Locations::inactiveRecord($id);

        return redirect()->route('admin.locations.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('locations_active')) {
            return abort(401);
        }

        Locations::activeRecord($id);

        return redirect()->route('admin.locations.index');
    }

    /**
     * function for index Sort Order.
     */
    public function sortorder()
    {
        if (!Gate::allows('locations_sort')) {
            return abort(401);
        }
        $location = DB::table('locations')->whereNull('deleted_at')->whereSlug('custom')->where(['account_id' => Auth::User()->account_id])->orderby('sort_no', 'ASC')->get();
        return view('admin.locations.Sort', compact('location'));
    }

    /**
     * function for Sort Order.
     */
    public function sortorder_save()
    {
        if (!Gate::allows('locations_sort')) {
            return abort(401);
        }

        $locatoion = DB::table('locations')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_no', 'ASC')->get();
        $itemID = Input::get('itemID');
        $itemIndex = Input::get('itemIndex');
        if ($itemID) {
            foreach ($locatoion as $locatoion) {
                $sort = DB::table('locations')->where('id', '=', $itemID)->update(array('sort_no' => $itemIndex));
                $myarray = ['status' => "Data Sort Successfully"];
                return response()->json($myarray);
            }
        } else {
            $myarray = ['status' => "Data Not Sort"];
            return response()->json($myarray);
        }
    }

    /**
     * Store a newly created Location and checked attribute exists or not.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        if (!Gate::allows('locations_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        return response()->json(array(
            'status' => 1,
            'message' => 'Record has been verified successfully.',
        ));
    }

    /**
     * updated Location and verify edit attribute exists or not
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function verify_edit(Request $request)
    {
        if (!Gate::allows('locations_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);
        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        return response()->json(array(
            'status' => 1,
            'message' => 'Record has been verified successfully.',
        ));
    }

}
