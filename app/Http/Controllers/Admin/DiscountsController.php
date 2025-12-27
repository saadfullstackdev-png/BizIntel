<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Filters;
use App\Models\DiscountApproval;
use App\Models\DiscountHasLocations;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use DB;
use App\Models\Discounts;
use App\Models\Locations;
use App\Models\Services;
use App\Helpers\NodesTree;
use Auth;
use PHPUnit\Util\Filter;
use Validator;
use Session;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use App\Helpers\Widgets\LocationsWidget;
use App\Helpers\Widgets\ServiceWidget;
use Config;

class DiscountsController extends Controller
{
    /**
     * Display a listing of the discount in datatable.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('discounts_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'discounts');

        $locations = Locations::getlocation();
        $locations->prepend('All', '');

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);

        $Services = $parentGroups->nodeList;

        return view('admin.discounts.index', compact('locations', 'Services', 'filters'));
    }

    /**
     * Show the form for creating a new Discount.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('discounts_create')) {
            return abort(401);
        }
        $discount = new \stdClass();
        $discount->service_id = null;
        $discount->location_id = null;

        $locations = Locations::getActiveSorted();

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id, true, true);
        $parentGroups->toList($parentGroups, -1);

        $Services = $parentGroups->nodeList;

        $discountServices = array();

        return view('admin.discounts.create', compact('discount', 'locations', 'Services', 'discountServices'));
    }

    /**
     * Store a newly created discount in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('discounts_create')) {
            return abort(401);
        }
        $validator = $this->verifyFields($request);
        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        // Now we need to check discount name already exist or not
        $discount_info = Discounts::where('name', '=', $request->name)->first();

        if ($discount_info) {
            return response()->json(array(
                'status' => 0,
                'message' => array('Discount name already exist!'),
            ));
        } else {
            $data = $request->all();
            $data['created_by'] = auth()->user()->id;
            $data['updated_by'] = auth()->user()->id;
            $data['account_id'] = session('account_id');
            if ($request->slug == 'custom' || $request->slug == 'default' || $request->slug == 'promotion' || $request->slug == 'special' || $request->slug == 'periodic') {
                $data['pre_days'] = 0;
                $data['post_days'] = 0;
            }

            if (Input::get('active') == null) {
                $data['active'] = '0';
            }

            if ($request->start <= $request->end) {

                if (Discounts::createDiscount($data)) {
                    flash('Record has been created successfully.')->success()->important();

                    return response()->json(array(
                        'status' => 1,
                        'message' => 'Record has been created successfully.',
                    ));
                } else {
                    return response()->json(array(
                        'status' => 0,
                        'message' => 'Something went wrong, please try again later.',
                    ));
                }
            } else {
                return response()->json(array(
                    'status' => 0,
                    'message' => array('Date range invalid, Kindly define again'),
                ));
            }
        }
    }

    /**
     * Validate form fields
     *
     * @param \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'amount' => 'required',
            'start' => 'required',
            'end' => 'required',
        ]);
    }

    /**
     * Display the discount in datatable form.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {

        $filename = 'discounts';
        $apply_filter = false;
        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, $filename);
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $serviceData = Services::all();
        $locationdata = Locations::all();

        $orderBy = 'created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        $where = array();

        if (Auth::user()->account_id && Auth::user()->account_id != '') {
            $where[] = array(
                'account_id',
                '=',
                Auth::user()->account_id
            );
            Filters::put(Auth::User()->id, $filename, 'account_id', Auth::user()->account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'account_id')
                    );
                }
            }
        }

        if ($request->get('name') && $request->get('name') != '') {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
            );
            Filters::put(Auth::User()->id, $filename, 'name', $request->get('name'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'name');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'name')) {
                    $where[] = array(
                        'name',
                        'like',
                        '%' . Filters::get(Auth::User()->id, $filename, 'name') . '%'
                    );
                }
            }
        }

        if ($request->get('type') && $request->get('type') != '') {
            $where[] = array(
                'type',
                'like',
                '%' . $request->get('type') . '%'
            );
            Filters::put(Auth::User()->id, $filename, 'type', $request->get('type'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'type');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'type')) {
                    $where[] = array(
                        'type',
                        'like',
                        '%' . Filters::get(Auth::User()->id, $filename, 'type') . '%'
                    );
                }
            }
        }

        if ($request->get('amount') && $request->get('amount') != '') {
            $where[] = array(
                'amount',
                'like',
                '%' . $request->get('amount') . '%'
            );
            Filters::put(Auth::User()->id, $filename, 'amount', $request->get('amount'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'amount');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'amount')) {
                    $where[] = array(
                        'amount',
                        'like',
                        '%' . Filters::get(Auth::User()->id, $filename, 'amount') . '%'
                    );
                }
            }
        }

        if ($request->get('discount_type') && $request->get('discount_type') != '') {
            $where[] = array(
                'discount_type',
                '=',
                $request->get('discount_type')
            );
            Filters::put(Auth::User()->id, $filename, 'discount_type', $request->get('discount_type'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'discount_type');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'discount_type')) {
                    $where[] = array(
                        'discount_type',
                        '=',
                        Filters::get(Auth::user()->id, $filename, 'discount_type')
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
            Filters::put(Auth::User()->id, $filename, 'created_from', $request->get('created_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'created_from')) {
                    $where[] = array(
                        'created_at',
                        '>=',
                        Filters::get(Auth::User()->id, $filename, 'created_from')
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
            Filters::put(Auth::User()->id, $filename, 'created_to', $request->get('created_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'created_to')) {
                    $where[] = array(
                        'created_at',
                        '<=',
                        Filters::get(Auth::User()->id, $filename, 'created_to')
                    );
                }
            }
        }
        if ($request->get('slug') && $request->get('slug') != '') {
            $where[] = array(
                'slug',
                '=',
                $request->get('slug')
            );
            Filters::put(Auth::User()->id, $filename, 'slug', $request->get('slug'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'slug');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'slug')) {
                    $where[] = array(
                        'slug',
                        '=',
                        Filters::get(Auth::user()->id, $filename, 'slug')
                    );
                }
            }
        }
        if ($request->get('startdate') && $request->get('startdate') != '') {
            $where[] = array(
                'start',
                '>=',
                $request->get('startdate')
            );
            Filters::put(Auth::user()->id, $filename, 'startdate', $request->get('startdate'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'startdate');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'startdate')) {
                    $where[] = array(
                        'start',
                        '>=',
                        Filters::get(Auth::user()->id, $filename, 'startdate')
                    );
                }
            }
        }

        if ($request->get('enddate') && $request->get('enddate') != '') {
            $where[] = array(
                'end',
                '<=',
                $request->get('enddate')
            );
            Filters::put(Auth::user()->id, $filename, 'enddate', $request->get('enddate'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'enddate');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'enddate')) {
                    $where[] = array(
                        'end',
                        '<=',
                        Filters::get(Auth::user()->id, $filename, 'enddate')
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
            Filters::put(Auth::user()->id, $filename, 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, $filename, 'status');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'status') == 0 || Filters::get(Auth::user()->id, $filename, 'status') == 1) {
                    if (Filters::get(Auth::user()->id, $filename, 'status') != null) {
                        $where[] = array(
                            'active',
                            '=',
                            Filters::get(Auth::user()->id, $filename, 'status')
                        );
                    }
                }
            }
        }

        $total_query = Discounts::select('id');
        if (count($where)) {
            $total_query->where($where);
        }
        $iTotalRecords = $total_query->count();

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $query = Discounts::select('*');
        if ($request->get('startdate') && $request->get('startdate') != '') {
            $query->whereDate('start', '>=', $request->get('startdate'));
        }
        if ($request->get('enddate') && $request->get('enddate') != '') {
            $query->whereDate('end', '<=', $request->get('enddate'));
        }

        if (count($where)) {
            $query->where($where);
        }
        $Discounts = $query->limit($iDisplayLength)->offset($iDisplayStart)->orderby($orderBy, $order)->get();

        if ($Discounts) {
            foreach ($Discounts as $discount) {
                $serviceExplod = explode(",", $discount->service_id);
                $locationExplod = explode(",", $discount->location_id);
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $discount->id . '"/><span></span></label>',
                    'name' => $discount->name,
                    'type' => $discount->type,
                    'amount' => $discount->amount,
                    'discount_type' => $discount->discount_type,
                    'start' => $discount->start ? \Carbon\Carbon::parse($discount->start)->format('D M, j Y') : null,
                    'end' => $discount->end ? \Carbon\Carbon::parse($discount->end)->format('D M, j Y') : null,
                    'slug' => $discount->slug,
                    'created_at' => Carbon::parse($discount->created_at)->format('F j,Y h:i A'),
                    'created_by' => $discount->created_by ? $discount->created_by_user->name : '-' ,
                    'updated_by' => $discount->updated_by ? $discount->updated_by_user->name : '-' ,
                    'status' => view('admin.discounts.status', compact('discount'))->render(),
                    'actions' => view('admin.discounts.actions', compact('discount'))->render(),
                );
            }
        }
        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $Discounts = Discounts::whereIn('id', $request->get('id'));
            if ($Discounts) {
                $Discounts->delete();
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for editing the specified discount information.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('discounts_edit')) {
            return abort(401);
        }

        $discount = Discounts::getData($id);

        if ($discount == null) {

            return view('error');

        } else {

            $discountServices = explode(",", $discount->service_id);

            if (!$discountServices) {

                $discountServices = array();
            }
            /* Create Nodes with Parents */
            $parentGroups = new NodesTree();
            $parentGroups->current_id = -1;
            $parentGroups->build(0, Auth::User()->account_id, true, true);
            $parentGroups->toList($parentGroups, -1);

            $Services = $parentGroups->nodeList;

            $locations = Locations::getActiveSorted();

            return view('admin.discounts.edit', compact('discount', 'locations', 'Services', 'discountServices'));
        }
    }

    /**
     * Update the specified discount in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('discounts_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }
        // Here we need to check discount name already exist except updated discount name
        $discount_info = Discounts::where('name', '=', $request->name)->get()->except($id);

        if (count($discount_info)) {
            return response()->json(array(
                'status' => 0,
                'message' => array('Discount name already exist!'),
            ));
        } else {
            $data = $request->all();
            $data['updated_by'] = auth()->user()->id;

            if ($request->slug == 'custom' || $request->slug == 'default' || $request->slug == 'promotion'|| $request->slug == 'special' || $request->slug == 'periodic') {
                $data['pre_days'] = 0;
                $data['post_days'] = 0;
            }

            if (Input::get('active') == null) {
                $data['active'] = '0';
            }

            if ($request->start <= $request->end) {

                if (Discounts::updateDiscount($data, $id)) {
                    flash('Record has been updated successfully.')->success()->important();

                    return response()->json(array(
                        'status' => 1,
                        'message' => 'Record has been updated successfully.',
                    ));
                } else {
                    return response()->json(array(
                        'status' => 0,
                        'message' => 'Something went wrong, please try again later.',
                    ));
                }
            } else {
                return response()->json(array(
                    'status' => 0,
                    'message' => array('Date range invalid, Kindly define again'),
                ));
            }
        }
    }

    /**
     * Inactive discount record from storage or database.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('discounts_inactive')) {
            return abort(401);
        }
        Discounts::inactiveRecord($id);

        return redirect()->route('admin.discounts.index');
    }

    /**
     * Active discount record from storage or database.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('discounts_active')) {
            return abort(401);
        }
        Discounts::activeRecord($id);

        return redirect()->route('admin.discounts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('discounts_destroy')) {
            return abort(401);
        }
        Discounts::deleteRecord($id);
        return redirect()->route('admin.discounts.index');

    }

    /**
     * Display lcoation to add service for doctor.
     *
     * @param int $id
     */
    public function displayDlocation($id)
    {
        if (!Gate::allows('discounts_allocate')) {
            return abort(401);
        }
        $discount = Discounts::find($id);

        $location = LocationsWidget::generateDropDownArray(session('account_id'));

        $discount_has_location = DiscountHasLocations::where('discount_id', '=', $discount->id)->get();

        return view('admin.discounts.location', compact('discount', 'location', 'discount_has_location'));
    }

    /**
     * display services against location id.
     *
     * @param request
     */
    public function getDservices(Request $request)
    {
        if (!Gate::allows('discounts_allocate')) {
            return abort(401);
        }
        $discount_info = Discounts::find($request->discount_id);

        if ($discount_info->discount_type == Config::get('constants.Service')) {
            $serive = ServiceWidget::generateServiceArrayArray($request, session('account_id'));
        } else {
            $serive = ServiceWidget::generateServiceArrayConsultancy($request, session('account_id'));
        }
        return response()->json(array(
            'status' => true,
            'd' => $serive,
            'locaiton_id_1' => $request->id,
        ));
    }

    /**
     * save services against location id.
     *
     * @param  $request
     */
    public function saveDservices(Request $request)
    {
        if (!Gate::allows('discounts_allocate')) {
            return abort(401);
        }

        $myString = $request->id;
        $myArray = explode(',', $myString);
        $data = [];

        $data['discount_id'] = $request->discount_id;
        $data['location_id'] = $myArray[0];
        $data['service_id'] = $myArray[1];

        $checked = DiscountHasLocations::where([
            ['location_id', '=', $myArray[0]],
            ['service_id', '=', $myArray[1]],
            ['discount_id', '=', $request->discount_id]
        ])->get();

        if (count($checked) == '0') {

            $record = DiscountHasLocations::create($data);

            $record_location_name = $record->location->city->name . "-" . $record->location->name;
            $record_service_name = $record->service->name;

            $myarray = ['record' => $record, 'record_locaiton_name' => $record_location_name, 'record_service_name' => $record_service_name];

            return response()->json(array(
                'status' => true,
                'mydata' => $myarray
            ));

        } else {
            return response()->json(array(
                'status' => false,
                'mydata' => null
            ));
        }
    }

    /**
     * delete serive
     *
     * @param request
     */
    public function deleteDservice(Request $request)
    {

        if (!Gate::allows('discounts_allocate')) {
            return abort(401);
        }

        DiscountHasLocations::find($request->id)->delete();
        return response()->json($request->id);

    }

    /**
     * Display application user with discount to decide who can approve special discount.
     *
     * @param int $id
     */
    public function displayApproval($id)
    {
        if (!Gate::allows('discounts_approval')) {
            return abort(401);
        }

        $discount = Discounts::find($id);

        $users = User::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('Select a User', '');

        $discount_approvals = DiscountApproval::where('discount_id', '=', $discount->id)->get();

        return view('admin.discounts.approval', compact('discount', 'users', 'discount_approvals'));
    }

    /**
     * save services against location id.
     *
     * @param  $request
     */
    public function saveApproval(Request $request)
    {
        if (!Gate::allows('discounts_approval')) {
            return abort(401);
        }

        $approval = DiscountApproval::where([
            ['user_id', '=', $request->user_id],
            ['discount_id', '=', $request->discount_id]
        ])->first();

        if(!$approval) {

            $data = $request->all();

            $record = DiscountApproval::create($data);

            $myarray = ['record' => $record, 'user' => $record->user->name, 'discount' => $record->discount->name];

            return response()->json(array(
                'status' => true,
                'mydata' => $myarray
            ));
        }  else {
            return response()->json(array(
                'status' => false,
                'mydata' => null
            ));
        }
    }

    /**
     * delete serive
     *
     * @param request
     */
    public function deleteApproval(Request $request)
    {

        if (!Gate::allows('discounts_approval')) {
            return abort(401);
        }

        DiscountApproval::find($request->id)->delete();
        return response()->json($request->id);

    }
}
