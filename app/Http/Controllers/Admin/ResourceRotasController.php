<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Filters;
use App\Models\Appointments;
use App\Models\Regions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use DB;
use App\Models\Resources;
use Session;
use Auth;
use Validator;
use App\Models\Locations;
use App\Models\ResourceTypes;
use App\Models\ResourceHasRota;
use App\Models\Cities;
use App\Helpers\ACL;
use Carbon\Carbon;
use App\Models\ResourceHasRotaDays;
use App\Models\Doctors;
use App\Helpers\Widgets\RotaManagement;


class ResourceRotasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('resourcerotas_manage')) {
            return abort('401');
        }

        $filters = Filters::all(Auth::User()->id, 'resourcehasrota');

        $resourcetype = ResourceTypes::getResourceType();
        $resourcetype->prepend('All', '');

        $location = Locations::getActiveSorted(ACL::getUserCentres());
        $location->prepend('All', '');

        $city = Cities::getActiveSortedFeatured(ACL::getUserCities());
        $city->prepend('All', '');

        $regions = Regions::getActiveSorted(ACL::getUserRegions());;
        $regions->prepend('Select a Region', '');

        return view('admin.resourcerotas.index', compact('resourcetype', 'location', 'city', 'regions', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('resourcerotas_create')) {
            return abort('401');
        }

        $resourcetype = ResourceTypes::getResourceforrota();

        $cities = Cities::getActiveFeaturedOnly(ACL::getUserCities(), Auth::User()->account_id)->get();
        if ($cities) {
            $cities = $cities->pluck('full_name', 'id');
        }
        $cities->prepend('Select a City', '');

        return view('admin.resourcerotas.create', compact('resourcetype', 'cities'));
    }

    /**
     * get the location against city id.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function load_location(Request $request)
    {

        if ($request->get("city_id")) {
            $locations = Locations::getActiveRecordsByCity($request->get("city_id"), ACL::getUserCentres(), Auth::User()->account_id);
            return response()->json(array(
                'status' => 1,
                'locations' => $locations
            ));
        } else {
            return response()->json(array(
                'status' => 0,
            ));
        }
    }

    /**
     * get the doctors and machine against location id.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function load_doctor_and_Machine(Request $request)
    {

        if ($request->get("location_id")) {
            $doctors = Doctors::getActiveOnly($request->get("location_id"));
            $machine = Resources::getActiveOnly($request->get("location_id"));
            return response()->json(array(
                'status' => 1,
                'doctors' => $doctors,
                'machine' => $machine
            ));
        } else {
            return response()->json(array(
                'status' => 0,
            ));
        }
    }

    /**
     * Store a newly created resource rota in storage/Database.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('resourcerotas_create')) {
            return abort(401);
        }
        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $response = ResourceHasRota::createRecord($request, Auth::User()->account_id);

        if ($response['status']) {
            flash($response['message'])->success()->important();
        }
        return response()->json($response);
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
            'start' => 'required',
            'end' => 'required',

        ]);
    }

    /**
     * The function for display resoruce rota in datatable
     *
     * @param  Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {

        $apply_filter = false;
        if($request->get('action')) {
            $action = $request->get('action');
            if(isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'resourcehasrota');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $resourcehasrota = ResourceHasRota::getBulkData($request->get('id'));
            if ($resourcehasrota) {
                foreach ($resourcehasrota as $resourcehasrota) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!ResourceHasRota::isChildExists($resourcehasrota->id, Auth::User()->account_id)) {
                        $resourcehasrota->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        $orderBy = 'created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        $where = array();
        $wherename = array();

        if ($request->get('resource_type_id') && $request->get('resource_type_id') != '') {
            $where[] = array(
                'resource_has_rota.resource_type_id',
                '=',
                $request->get('resource_type_id')
            );
            Filters::put(Auth::User()->id, 'resourcehasrota', 'resource_type_id', $request->get('resource_type_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'resource_type_id');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'resource_type_id')) {
                    $where[] = array(
                        'resource_has_rota.resource_type_id',
                        '=',
                        Filters::get(Auth::User()->id, 'resourcehasrota', 'resource_type_id')
                    );
                }
            }
        }

        if ($request->get('region_id') && $request->get('region_id') != '') {
            $where[] = array(
                'resource_has_rota.region_id',
                '=',
                $request->get('region_id')
            );
            Filters::put(Auth::User()->id, 'resourcehasrota', 'region_id', $request->get('region_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'region_id');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'region_id')) {
                    $where[] = array(
                        'resource_has_rota.region_id',
                        '=',
                        Filters::get(Auth::User()->id, 'resourcehasrota', 'region_id')
                    );
                }
            }
        }

        if ($request->get('city_id') && $request->get('city_id') != '') {
            $where[] = array(
                'resource_has_rota.city_id',
                '=',
                $request->get('city_id')
            );
            Filters::put(Auth::User()->id, 'resourcehasrota', 'city_id', $request->get('city_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'city_id');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'city_id')) {
                    $where[] = array(
                        'resource_has_rota.city_id',
                        '=',
                        Filters::get(Auth::User()->id, 'resourcehasrota', 'city_id')
                    );
                }
            }
        }

        if ($request->get('location_id') && $request->get('location_id') != '') {
            $where[] = array(
                'resource_has_rota.location_id',
                '=',
                $request->get('location_id')
            );
            Filters::put(Auth::User()->id, 'resourcehasrota', 'location_id', $request->get('location_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'location_id');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'location_id')) {
                    $where[] = array(
                        'resource_has_rota.location_id',
                        '=',
                        Filters::get(Auth::User()->id, 'resourcehasrota', 'location_id')
                    );
                }
            }
        }

        if (session()->has('account_id') && session()->get('account_id') != ''){
            $where[] = array(
                'resource_has_rota.account_id',
                '=',
                session('account_id')
            );
            Filters::put(Auth::User()->id, 'resourcehasrota', 'account_id', session('account_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'account_id')) {
                    $where[] = array(
                        'resource_has_rota.account_id',
                        '=',
                        Filters::get(Auth::User()->id, 'resourcehasrota', 'account_id')
                    );
                }
            }
        }

        if ($request->get('resourcename') && $request->get('resourcename') != '') {
            $wherename[] = array(
                'resources.name',
                'like',
                '%' . $request->get('resourcename') . '%'
            );
            Filters::put(Auth::User()->id, 'resourcehasrota', 'resourcename', $request->get('resourcename'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'resourcename');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'resourcename')) {
                    $wherename[] = array(
                        'resources.name',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'resourcehasrota', 'resourcename') . '%'
                    );
                }
            }
        }

        if ( $request->get('startdate') && $request->get('startdate') != ''){
            $where[] = array(
                'resource_has_rota.start',
                '>=',
                $request->get('startdate')
            );
            Filters::put(Auth::user()->id , 'resourcehasrota', 'startdate', $request->get('startdate'));
        } else {
            if ($apply_filter){
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'startdate');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'startdate')) {
                    $where[] = array(
                        'resource_has_rota.start',
                        '>=',
                        Filters::get(Auth::user()->id , 'resourcehasrota', 'startdate')
                    );
                }
            }
        }

        if ( $request->get('enddate') && $request->get('enddate') != ''){
            $where[] = array(
                'resource_has_rota.end',
                '<=',
                $request->get('enddate')
            );
            Filters::put(Auth::user()->id , 'resourcehasrota', 'enddate', $request->get('enddate'));
        } else {
            if ($apply_filter){
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'enddate');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'enddate')) {
                    $where[] = array(
                        'resource_has_rota.end',
                        '<=',
                        Filters::get(Auth::user()->id , 'resourcehasrota', 'enddate')
                    );
                }
            }
        }

        if ( $request->get('created_from') && $request->get('created_from') != '') {
            $where[] = array(
                'resource_has_rota.created_at',
                '>=',
                $request->get('created_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, 'resourcehasrota', 'created_from', $request->get('created_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'created_from')) {
                    $where[] = array(
                        'resource_has_rota.created_at',
                        '>=',
                        Filters::get(Auth::User()->id, 'resourcehasrota', 'created_from') . ' 00:00:00'
                    );
                }
            }
        }

        if ( $request->get('created_to') && $request->get('created_to') != '') {
            $where[] = array(
                'resource_has_rota.created_at',
                '<=',
                $request->get('created_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, 'resourcehasrota', 'created_to', $request->get('created_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'resourcehasrota', 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, 'resourcehasrota', 'created_to')) {
                    $where[] = array(
                        'resource_has_rota.created_at',
                        '<=',
                        Filters::get(Auth::User()->id, 'resourcehasrota', 'created_to') . ' 23:59:59'
                    );
                }
            }
        }

        if ( $request->get('status') && $request->get('status') != null || $request->get('status') == 0 && $request->get('status') != null ){
            $where[] = array(
                'resource_has_rota.active',
                '=',
                $request->get('status')
            );
            Filters::put(Auth::user()->id, 'resourcehasrota', 'status', $request->get('status'));
        } else {
            if ( $apply_filter ){
                Filters::forget( Auth::user()->id, 'resourcehasrota', 'status');
            } else {
                if ( Filters::get(Auth::user()->id, 'resourcehasrota', 'status') == 0 || Filters::get(Auth::user()->id, 'resourcehasrota', 'status') == 1){
                    if ( Filters::get(Auth::user()->id, 'resourcehasrota', 'status') != null ){
                        $where[] = array(
                            'resource_has_rota.active',
                            '=',
                            Filters::get( Auth::user()->id, 'resourcehasrota', 'status')
                        );
                    }
                }
            }
        }


        $total_query = Resources::join('resource_has_rota', 'resources.id', '=', 'resource_has_rota.resource_id')->where($wherename)->whereIn('resource_has_rota.location_id', ACL::getUserCentres())->select('resource_has_rota.id');

        if (count($where)) {
            $total_query->where($where);
        }
        $iTotalRecords = $total_query->count();

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $query = Resources::join('resource_has_rota', 'resources.id', '=', 'resource_has_rota.resource_id')->where($wherename)->whereIn('resource_has_rota.location_id', ACL::getUserCentres())->select('resource_has_rota.*');

        if ($request->get('startdate') && $request->get('startdate') != '') {
            $query->whereDate('resource_has_rota.start', '>=', $request->get('startdate'));
        }
        if ($request->get('enddate') && $request->get('enddate') != '') {
            $query->whereDate('resource_has_rota.end', '<=', $request->get('enddate'));
        }

        if (count($where)) {
            $query->where($where);
        }

        $resourcehasrota = $query->limit($iDisplayLength)->offset($iDisplayStart)->orderby($orderBy, $order)->get();
        $Regions = Regions::getAllRecordsDictionary(Auth::User()->account_id);

        if ($resourcehasrota) {
            foreach ($resourcehasrota as $resourcerota) {
                $resourcetypeinfo = ResourceTypes::where('id', '=', $resourcerota->resource_type_id)->first();
                $resourceinfo = Resources::where('id', '=', $resourcerota->resource_id)->first();
                $city = Cities::where('id', '=', $resourcerota->city_id)->first();
                $location = Locations::where('id', '=', $resourcerota->location_id)->first();
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $resourcerota->id . '"/><span></span></label>',
                    'name' => $resourceinfo->name,
                    'type' => $resourcetypeinfo->name,
                    'region' => (array_key_exists($resourcerota->region_id, $Regions)) ? $Regions[$resourcerota->region_id]->name : 'N/A',
                    'city' => $city->name,
                    'location' => $location->name,
                    'from' => $resourcerota->start ? \Carbon\Carbon::parse($resourcerota->start)->format('D M, j Y') : null,
                    'to' => $resourcerota->end ? \Carbon\Carbon::parse($resourcerota->end)->format('D M, j Y') : null,
                    'created_at' => Carbon::parse($resourcerota->created_at)->format('F j,Y h:i A'),
                    'status' => view('admin.resourcerotas.status', compact('resourcerota'))->render(),
                    'actions' => view('admin.resourcerotas.actions', compact('resourcerota'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('resourcerotas_inactive')) {
            return abort(401);
        }
        ResourceHasRota::inactiveRecord($id);


        return redirect()->route('admin.resourcerotas.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('resourcerotas_active')) {
            return abort(401);
        }

        ResourceHasRota::activeRecord($id);

        return redirect()->route('admin.resourcerotas.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('resourcerotas_edit')) {
            return abort(401);
        }
        $resourceRota = ResourceHasRota::find($id);
        $citie = Cities::find($resourceRota->city_id);
        $city = $resourceRota->city->name;
        $location = $resourceRota->location->name;
        $resource_name = Resources::where('id', '=', $resourceRota->resource_id)->first();
        if ($resourceRota->copy_all == '0') {
            $week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
            foreach ($week as $day) {
                if ($resourceRota[$day]) {

                    $tem = explode(',', $resourceRota[$day]);
                    $resourceRota['time_f_' . $day] = Carbon::parse($tem[0])->format('h:i A');
                    $resourceRota['time_to_' . $day] = Carbon::parse($tem[1])->format('h:i A');

                    if($resourceRota[$day.'_off']){
                        $break = explode(',', $resourceRota[$day.'_off']);
                        $resourceRota['break_from_' . $day] = Carbon::parse($break[0])->format('h:i A');
                        $resourceRota['break_to_' . $day] = Carbon::parse($break[1])->format('h:i A');
                    } else{
                        $resourceRota['break_from_' . $day] = null;
                        $resourceRota['break_to_' . $day] = null;
                    }

                } else {
                    $resourceRota[$day . 'checked'] = 'on';
                }
            }
        }
        if ($resourceRota->copy_all == '1') {
            $week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
            foreach ($week as $day) {
                $tem = explode(',', $resourceRota->monday);
                $resourceRota['time_f_' . $day] = Carbon::parse($tem[0])->format('h:i A');
                $resourceRota['time_to_' . $day] = Carbon::parse($tem[1])->format('h:i A');
                if($resourceRota[$day.'_off']){
                    $break = explode(',', $resourceRota->monday_off);
                    $resourceRota['break_from_' . $day] = Carbon::parse($break[0])->format('h:i A');
                    $resourceRota['break_to_' . $day] = Carbon::parse($break[1])->format('h:i A');
                } else {
                    $resourceRota['break_from_' . $day] = null;
                    $resourceRota['break_to_' . $day] = null;
                }
            }
        }
        return view('admin.resourcerotas.edit', compact('resourceRota', 'resource_name', 'city', 'citie', 'location'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('resourcerotas_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $resourcerota = ResourceHasRota::find($id);

        if ($resourcerota->end <= $request->end) {

            $response = ResourceHasRota::updateRecord($id, $request, Auth::User()->account_id);
            if ($response['status']) {

                flash($response['message'])->success()->important();
            }
            return response()->json($response);

        } else {

            return array(
                'status' => 0,
                'message' => array('Your To date must be equal or greater than your previous To date ' . $resourcerota->end),
            );
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('resourcerotas_destroy')) {
            return abort(401);
        }
        ResourceHasRota::deleteRecord($id);

        return redirect()->route('admin.resourcerotas.index');
    }

    /**
     * Get information for calender view ajax base
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getcalenderinfoevents($id)
    {
        if (!Gate::allows('resourcerotas_manage')) {
            return abort('401');
        }

        $resourceRota = ResourceHasRota::find($id);

        $resource = Resources::where('id', '=', $resourceRota->resource_id)->first();

        $rotahasDays = ResourceHasRotaDays::where('resource_has_rota_id', '=', $resourceRota->id)->get();

        if ($rotahasDays) {
            $index = 0;
            foreach ($rotahasDays as $days) {

                $start_rotadays = Carbon::parse($days->start_time);

                $end_rotadays = Carbon::parse($days->end_time);

                $date = $days->date;

                $today_date = Carbon::now()->toDateString();
                if($date<$today_date){
                    $checked = 0;
                } else {
                    $checked = 1;
                }
                $dayname = strtolower(Carbon::parse($date)->format('l'));

                $difference_rotadays = $start_rotadays->diffInMinutes($end_rotadays);

                $resourcehasrotainfo = ResourceHasRota::where('id', '=', $days->resource_has_rota_id)->first();

                $tem = explode(',', $resourcehasrotainfo[$dayname]);

                if (count($tem) <= '1') {
                    $tem = null;
                }
                $records[$index] = array(
                    'id' => $days->id,
                    'date' => $days->date,
                    'start_time' => $days->start_time,
                    'end_time' => $days->end_time,
                    'start_off' => $days->start_off,
                    'end_off' => $days->end_off,
                    'title' => view('admin.resourcerotas.calender_action', compact('days'))->render(),
                    'start' => $days->date,
                    'color' => view('admin.resourcerotas.calender_color', compact('difference_rotadays', 'tem'))->render(),
                    'checked' => $checked
                );
                $index++;
            }
        }
        return Response()->json($records);
    }

    /**
     * Get information for calender view
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getcalenderinfo($id)
    {
        if (!Gate::allows('resourcerotas_manage')) {
            return abort('401');
        }
        $resourcehasrota = ResourceHasRota::getData($id);

        $resource = Resources::where('id', '=', $resourcehasrota->resource_id)->first();

        if ($resourcehasrota == null) {

            return view('error_full');

        } else {

            return view('admin.resourcerotas.calender', compact('id', 'resource'));
        }
    }

    /**
     * update information of Resource days in resource has rotas days
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function store_calender_edit()
    {

        if (!Gate::allows('resourcerotas_manage')) {

            return abort('401');
        }
        $rotahasdays = ResourceHasRotaDays::where('id', '=', Input::get('resource_days_id'))->first();

        $resourceid = ResourceHasRota::where('id', '=', $rotahasdays->resource_has_rota_id)->first();

        if (Input::get('dayElement') != 'on') {

            if(Input::get('start_time') && Input::get('end_time')){
                if (Input::get('start_time') == Input::get('end_time')) {
                    flash('Time range must be different, kindly define again.')->warning()->important();
                    return redirect()->route('admin.resourcerotas.calender', ['id' => $resourceid->id]);
                }
            } else {
                flash('From or To require, kindly define again.')->warning()->important();
                return redirect()->route('admin.resourcerotas.calender', ['id' => $resourceid->id]);
            }

            if(Input::get('start_off') && Input::get('end_off')){
                if(Input::get('start_off') == Input::get('end_off')){
                    flash('Time range must be different, kindly define again.')->warning()->important();
                    return redirect()->route('admin.resourcerotas.calender', ['id' => $resourceid->id]);
                } else {
                    if (
                        strtotime(Input::get('start_off')) >= strtotime(Input::get('start_time')) &&
                        strtotime(Input::get('end_off')) <= strtotime(Input::get('end_time'))
                    ) {

                        $start_off = Input::get('start_off');
                        $end_off = Input::get('end_off');

                    } else {
                        flash('Break time must be between From and To, Kindly Define again.')->warning()->important();
                        return redirect()->route('admin.resourcerotas.calender', ['id' => $resourceid->id]);
                    }
                }
            } else {
                if(!Input::get('start_off') && !Input::get('end_off')){
                    $start_off = null;
                    $end_off = null;
                }
                if(Input::get('start_off') || Input::get('end_off')){
                    flash('From or To require, kindly define again.')->warning()->important();
                    return redirect()->route('admin.resourcerotas.calender', ['id' => $resourceid->id]);
                }
            }
        }
        if (Input::get('dayElement') == null) {

            $start_timestamp = Carbon::parse($rotahasdays->date . ' ' . Input::get('start_time'))->format('Y-m-d H:i') . ':00';
            $end_timestamp = Carbon::parse($rotahasdays->date . ' ' . Input::get('end_time'))->format('Y-m-d H:i') . ':00';

            /*First I checked For doctor*/
            if ($resourceid->resource_type_id == 2) {
                $rota_appointments = Appointments::where('resource_has_rota_day_id', '=', $rotahasdays->id)->wheredate('scheduled_date', '=', $rotahasdays->date)->select('id', 'scheduled_date', 'scheduled_time')->get()->toArray();
            }
            /*Second I checked for machine*/
            if ($resourceid->resource_type_id == 1) {
                $rota_appointments = Appointments::where('resource_has_rota_day_id_for_machine', '=', $rotahasdays->id)->wheredate('scheduled_date', '=', $rotahasdays->date)->select('id', 'scheduled_date', 'scheduled_time')->get()->toArray();
            }
            $not_allow = false;
            if (count($rota_appointments)) {
                foreach ($rota_appointments as $rota_appointment) {
                    if ($rota_appointment['scheduled_time'] && Input::get('start_time') && Input::get('end_time')) {
                        if (!ResourceHasRota::checkTime(Carbon::parse($rota_appointment['scheduled_time'])->format('h:i A'), Input::get('start_time'), Input::get('end_time'))) {
                            $not_allow = true;
                            flash('Provided rota timings are conflicts with appointments. Unable to update rota.')->warning()->important();
                            break;
                        }
                        if (ResourceHasRota::checkTime(Carbon::parse($rota_appointment['scheduled_time'])->format('h:i A'), Input::get('start_off'), Input::get('end_off'))) {
                            $not_allow = true;
                            flash('Provided rota break timings are conflicts with appointments. Unable to update rota.')->warning()->important();
                            break;
                        }
                    }
                }
            }
            if ($not_allow) {
                return redirect()->route('admin.resourcerotas.calender', ['id' => $resourceid->id]);
            } else {
                $rotahasdays->update([
                        'start_time' => Input::get('start_time'),
                        'end_time' => Input::get('end_time'),
                        'start_timestamp' => $start_timestamp,
                        'end_timestamp' => $end_timestamp,
                        'start_off' => $start_off,
                        'end_off' => $end_off
                ]);
            }
        } else {
            /*First I checked For doctor*/
            if ($resourceid->resource_type_id == 2) {
                $appointmentinformation = Appointments::where('resource_has_rota_day_id', '=', $rotahasdays->id)->wheredate('scheduled_date', '=', $rotahasdays->date)->get();
            }
            /*Second I checked for machine*/
            if ($resourceid->resource_type_id == 1) {
                $appointmentinformation = Appointments::where('resource_has_rota_day_id_for_machine', '=', $rotahasdays->id)->wheredate('scheduled_date', '=', $rotahasdays->date)->get();
            }
            /*Now I Checked we can define leave or not*/
            if (count($appointmentinformation) == 0) {
                $rotahasdays->update([
                    'start_time' => null,
                    'end_time' => null,
                    'start_off' => null,
                    'end_off' => null,
                ]);
            } else {
                flash('Rota use in appointment, kindly define again.')->warning()->important();
                return redirect()->route('admin.resourcerotas.calender', ['id' => $resourceid->id]);
            }
        }
        flash('Record has been updated successfully.')->success()->important();

        return redirect()->route('admin.resourcerotas.calender', ['id' => $resourceid->id]);
    }


}
