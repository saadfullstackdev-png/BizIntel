<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\GroupsTree;
use App\Helpers\Widgets\LocationsWidget;
use App\Helpers\Widgets\ServiceWidget;
use App\Models\Cities;
use App\Models\Locations;
use App\Models\Services;
use App\Models\UserHasLocations;
use App\Models\Doctors;
use App\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsersRequest;
use App\Http\Requests\Admin\UpdateUsersRequest;
use App\Http\Requests\Admin\UpdateUserPasswordRequest;
use App\Models\UserTypes;
use DB;
use Session;
use App\Helpers\GeneralFunctions;
use App\Models\ResourceTypes;
use App\Models\Resources;
use App\Helpers\NodesTree;
use Auth;
use App\Models\DoctorHasServices;
use Config;
use Validator;
use App\Models\ServiceHasLocations;
use App\Models\AuditTrails;
use App\Models\DoctorHasLocations;
use Spatie\Permission\Models\Role;
use App\Models\RoleHasUsers;
use Carbon\Carbon;
use App\Helpers\Filters;


class DoctorsController extends Controller
{
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('doctors_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'doctors');
        $role = Role::get()->pluck('name', 'id');
        $role->prepend('All', '');

        return view('admin.doctors.index', compact('role', 'filters'));
    }

    /**
     * Display a User As Doctor  in datatables.
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
                Filters::flush(Auth::User()->id, 'doctors');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $users = User::getBulkData($request->get('id'));
            if ($users) {
                foreach ($users as $user) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!User::isExists($user->id, Auth::User()->account_id)) {
                        $user->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        $where = array();

        $orderBy = 'created_at';
        $order = 'desc';

        if ($request->get('order')) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            if ($orderBy == 'created_at') {
                $orderBy = 'users.created_at';
            }
            $order = $request->get('order')[0]['dir'];

            Filters::put(Auth::User()->id, 'doctors', 'order_by', $orderBy);
            Filters::put(Auth::User()->id, 'doctors', 'order', $order);
        } else {
            if (
                Filters::get(Auth::User()->id, 'doctors', 'order_by')
                && Filters::get(Auth::User()->id, 'doctors', 'order')
            ) {
                $orderBy = Filters::get(Auth::User()->id, 'doctors', 'order_by');
                $order = Filters::get(Auth::User()->id, 'doctors', 'order');

                if ($orderBy == 'created_at') {
                    $orderBy = 'users.created_at';
                }
            } else {
                $orderBy = 'created_at';
                $order = 'desc';
                if ($orderBy == 'created_at') {
                    $orderBy = 'users.created_at';
                }

                Filters::put(Auth::User()->id, 'doctors', 'order_by', $orderBy);
                Filters::put(Auth::User()->id, 'doctors', 'order', $order);
            }
        }

        if ($request->get('name')) {
            $where[] = array(
                'users.name',
                'like',
                '%' . $request->get('name') . '%'
            );
            Filters::put(Auth::User()->id, 'doctors', 'name', $request->get('name'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'doctors', 'name');
            } else {
                if (Filters::get(Auth::User()->id, 'doctors', 'name')) {
                    $where[] = array(
                        'users.name',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'doctors', 'name') . '%'
                    );
                }
            }
        }

        if ($request->get('email')) {
            $where[] = array(
                'users.email',
                'like',
                '%' . $request->get('email') . '%'
            );
            Filters::put(Auth::User()->id, 'doctors', 'email', $request->get('email'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'doctors', 'email');
            } else {
                if (Filters::get(Auth::User()->id, 'doctors', 'email')) {
                    $where[] = array(
                        'users.email',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'doctors', 'email') . '%'
                    );
                }
            }
        }

        if ($request->get('phone')) {
            $where[] = array(
                'users.phone',
                'like',
                '%' . GeneralFunctions::cleanNumber($request->get('phone')) . '%'
            );
            Filters::put(Auth::User()->id, 'doctors', 'phone', $request->get('phone'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'doctors', 'phone');
            } else {
                if (Filters::get(Auth::User()->id, 'doctors', 'phone')) {
                    $where[] = array(
                        'users.phone',
                        'like',
                        '%' . GeneralFunctions::cleanNumber(Filters::get(Auth::User()->id, 'doctors', 'phone')) . '%'
                    );
                }
            }
        }

        if ($request->get('gender')) {
            $where[] = array(
                'users.gender',
                '=',
                $request->get('gender')
            );
            Filters::put(Auth::User()->id, 'doctors', 'gender', $request->get('gender'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'doctors', 'gender');
            } else {
                if (Filters::get(Auth::User()->id, 'doctors', 'gender')) {
                    $where[] = array(
                        'users.gender',
                        '=',
                        Filters::get(Auth::User()->id, 'doctors', 'gender')
                    );
                }
            }
        }

        if ($request->get('role_id')) {
            $where[] = array(
                'role_has_users.role_id',
                '=',
                $request->get('role_id')
            );
            Filters::put(Auth::User()->id, 'doctors', 'role_id', $request->get('role_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'doctors', 'role_id');
            } else {
                if (Filters::get(Auth::User()->id, 'doctors', 'role_id')) {
                    $where[] = array(
                        'role_has_users.role_id',
                        '=',
                        Filters::get(Auth::User()->id, 'doctors', 'role_id')
                    );
                }
            }
        }

        if ($request->get('is_mobile') != '') {
            $where[] = array(
                'is_mobile',
                'like',
                '%' . $request->get('is_mobile') . '%'
            );
            Filters::put(Auth::User()->id, 'doctors', 'is_mobile', $request->get('is_mobile'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'doctors', 'is_mobile');
            } else {
                if (Filters::get(Auth::User()->id, 'doctors', 'is_mobile')) {
                    $where[] = array(
                        'is_mobile',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'doctors', 'is_mobile') . '%'
                    );
                }
            }
        }

        $where[] = array(
            'users.user_type_id',
            '=',
            Config::get('constants.asthatic_operator_id')
        );

        $where[] = array(
            'users.account_id',
            '=',
            Auth::User()->account_id
        );

        $where[] = array(
            'users.resource_type_id',
            '=',
            2
        );

        if ($request->get('created_from')) {
            $where[] = array(
                'users.created_at',
                '>=',
                $request->get('created_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, 'doctors', 'created_from', $request->get('created_from'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'doctors', 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, 'doctors', 'created_from')) {
                    $where[] = array(
                        'users.created_at',
                        '>=',
                        Filters::get(Auth::User()->id, 'doctors', 'created_from') . ' 00:00:00'
                    );
                }
            }
        }

        if ($request->get('created_to')) {
            $where[] = array(
                'users.created_at',
                '<=',
                $request->get('created_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, 'doctors', 'created_to', $request->get('created_to'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'doctors', 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, 'doctors', 'created_to')) {
                    $where[] = array(
                        'users.created_at',
                        '<=',
                        Filters::get(Auth::User()->id, 'doctors', 'created_to') . ' 23:59:59'
                    );
                }
            }
        }

        if ($request->get('status') && $request->get('status') != null || $request->get('status') == 0 && $request->get('status') != null) {
            $where[] = array(
                'users.active',
                '=',
                $request->get('status')
            );
            Filters::put(Auth::user()->id, 'doctors', 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'doctors', 'status');
            } else {
                if (Filters::get(Auth::user()->id, 'doctors', 'status') == 0 && Filters::get(Auth::user()->id, 'doctors', 'status') == 1) {
                    $where[] = array(
                        'users.active',
                        '=',
                        Filters::get(Auth::user()->id, 'doctors', 'status')
                    );
                }
            }
        }

        if (count($where)) {
            $iTotalRecords = count(DB::table('users')
                ->leftJoin('role_has_users', 'users.id', '=', 'role_has_users.user_id')
                ->groupBy('role_has_users.user_id')
                ->where($where)
                ->get());
        }

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        if (count($where)) {
            $Users = User::leftjoin('role_has_users', 'users.id', '=', 'role_has_users.user_id')
                ->groupBy('role_has_users.user_id')
                ->where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy, $order)->get();
        }
        if ($Users) {
            $index = 0;
            $ro = Role::select('id', 'name')->get()->getDictionary();
            foreach ($Users as $user) {

                $roles = '';
                foreach ($user->role_has_users as $role) {
                    if (array_key_exists($role->role_id, $ro)) {
                        $roles .= '<span class="label label-sm label-info">' . $ro[$role->role_id]->name . '</span>&nbsp;';
                    }
                }
                $records["data"][$index] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $user->id . '"/><span></span></label>',
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'gender' => view('admin.doctors.genderselection', compact('user'))->render(),
                    'role' => $roles,
                    'is_mobile' => ($user->is_mobile) ? 'Yes' : 'No',
                    'created_at' => Carbon::parse($user->created_at)->format('F j,Y h:i A'),
                    'status' => view('admin.doctors.status', compact('user'))->render(),
                    'actions' => view('admin.doctors.actions', compact('user'))->render(),
                );
                $index++;
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Validate new service
     */
    public function verify(Request $request)
    {
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
     * Validate old service
     */
    public function verifyUpdate(Request $request)
    {
        $validator = $this->verifyUpdateFields($request);

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
     * Show the form for creating new User As Doctors.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('doctors_create')) {
            return abort(401);
        }
        $doctor = new \stdClass();

        $doctor->gender = null;
        $doctor->phone = null;

        $userstype = UserTypes::getUserType_for_Doctor();
        $userstype->prepend('Select a User Type', '');

        $locations = Locations::where([
            ['account_id', '=', session('account_id')],
            ['active', '=', '1']
        ])->get()->pluck('full_address', 'id');

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id, true, true);
        $parentGroups->toList($parentGroups, -1);

        $Services = $parentGroups->nodeList;

        $DoctorServices = array();

        $roles = Role::get()->pluck('name', 'name');

        return view('admin.doctors.create', compact('locations', 'userstype', 'userstype', 'doctor', 'Services', 'DoctorServices', 'roles'));
    }

    /**
     * Store a newly created User as Doctor in storage or datatable.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('doctors_create')) {
            return abort(401);
        }

        $data = $request->all();

        $resourcetype_id = ResourceTypes::where('name', '=', 'doctor')->first();

        $data['resource_type_id'] = $resourcetype_id->id;
        $data['user_type_id'] = Config::get('constants.practitioner_id');
        $data['account_id'] = Auth::User()->account_id;
        $data['phone'] = GeneralFunctions::cleanNumber($request->phone);
        //Set Image
        if ($request->file('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $image_url = md5(time() . rand(0001, 9999) . rand(78599, 99999)) . ".$ext";
            $file->move('doctor_image', $image_url);
            $data['image_src'] = $image_url;
        }

        if (!isset($data['is_mobile'])) {
            $data['is_mobile'] = 0;
        } else if ($data['is_mobile'] == '') {
            $data['is_mobile'] = 0;
        }

        if ($user = User::createRecord($data)) {

            $roles = $request->input('roles') ? $request->input('roles') : [];
            $user->assignRole($roles);

            // Check if role exist and are set then assign role to users
            if ($request->get('roles') && is_array($request->get('roles'))) {
                $roles = $request->get('roles');
                $role_has_users = array();
                foreach ($roles as $role) {
                    $roleid = DB::table('roles')->select('id')->where('name', '=', $role)->first();
                    $role_has_users = array(
                        'role_id' => $roleid->id,
                        'user_id' => $user->id,
                    );
                    // Insert assigned role to users
                    RoleHasUsers::createRecord($role_has_users, $user);
                }
            }

            $resource = new Resources();
            $resource->name = $request->name;
            $resource->account_id = session('account_id');
            $resource->resource_type_id = $resourcetype_id->id;
            $resource->external_id = $user->id;
            $resource->save();
        }
        flash('Record has been created successfully.')->success()->important();

        return redirect()->route('admin.doctors.index');
    }

    /**
     * Show the form for changing password.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function changePassword($id)
    {
        if (!Gate::allows('doctors_change_password')) {
            return abort(401);
        }
        $user = User::getData($id);
        if ($user == null) {
            return view('error');
        } else {
            return view('admin.doctors.change_password', compact('user'));
        }
    }

    /**
     * Update User Password in storage or Database.
     */
    public function savePassword(Request $request)
    {
        if (!Gate::allows('doctors_change_password')) {
            return abort(401);
        }
        $data = array();
        $validator = $this->verifyPasswordFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }
        try {
            $id = decrypt($request->get('id'));
        } catch (DecryptException $e) {
            flash('Are you mad? what were you trying to do? :@.')->error()->important();
            return redirect()->back();
        }

        $data['password'] = bcrypt($request->get('password'));

        $result = User::updateRecord($data, $id);

        if ($result) {
            flash('Password has been changed successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => 'Password has been changed successfully.',
            ));
        }

        flash('Are you mad? what were you trying to do? :@.')->error()->important();

        return redirect()->back();
    }

    /**
     * Show the form for editing User.
     */
    public function edit($id)
    {
        if (!Gate::allows('doctors_edit')) {
            return abort(401);
        }
        $doctor = User::getData($id);

        if ($doctor == null) {
            return view('error');
        } else {

            $userstype = UserTypes::getUserType_for_Doctor();
            $userstype->prepend('Select a User Type', '');

            $user_has_locations = $doctor->user_has_locations->pluck("location_id");

            $DoctorServices = $doctor->doctor_has_services()->pluck('service_id')->toArray();

            if (!$user_has_locations) {
                $user_has_locations = array();
            }

            if (!$DoctorServices) {
                $DoctorServices = array();
            }

            /* Create Nodes with Parents */
            $parentGroups = new NodesTree();
            $parentGroups->current_id = -1;
            $parentGroups->build(0, Auth::User()->account_id, true, true);
            $parentGroups->toList($parentGroups, -1);

            $Services = $parentGroups->nodeList;

            $locations = Locations::where([
                ['account_id', '=', session('account_id')],
                ['active', '=', '1']
            ])->get()->pluck('full_address', 'id');

            $roles = Role::get()->pluck('name', 'name');

            return view('admin.doctors.edit', compact('doctor', 'user_has_locations', 'locations', 'userstype', 'DoctorServices', 'Services', 'roles'));
        }
    }

    /**
     * Update User in storage.
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('doctors_edit')) {
            return abort(401);
        }

        $user = User::findOrFail($id);

        $data = $request->all();
        $data['phone'] = GeneralFunctions::cleanNumber($request->phone);
        //Set Image
        if ($request->file('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $image_url = md5(time() . rand(0001, 9999) . rand(78599, 99999)) . ".$ext";
            $file->move('doctor_image', $image_url);
            $data['image_src'] = $image_url;
        }

        if (!isset($data['is_mobile'])) {
            $data['is_mobile'] = 0;
        } else if ($data['is_mobile'] == '') {
            $data['is_mobile'] = 0;
        }

        if ($user = User::updateRecord($data, $id)) {

            $roles = $request->input('roles') ? $request->input('roles') : [];
            $user->syncRoles($roles);

            // Check if locations exist and are set then assign centres to User
            if ($request->get('roles') && is_array($request->get('roles'))) {
                // Destroy if user has locations
                $user->role_has_users()->forceDelete();

                $roles = $request->get('roles');
                $role_has_users = array();
                foreach ($roles as $role) {
                    $roleid = DB::table('roles')->select('id')->where('name', '=', $role)->first();
                    $role_has_users = array(
                        'role_id' => $roleid->id,
                        'user_id' => $user->id,
                    );
                    // Insert assigned centres to User
                    RoleHasUsers::updateRecord($role_has_users, $user);
                }

            }
            $resource_doctor = Resources::where('external_id', '=', $user->id)->first();
            $resource_doctor->name = $request->name;
            $resource_doctor->save();
        }
        flash('Record has been updated successfully.')->success()->important();

        return redirect()->route('admin.doctors.index');
    }

    /**
     * Remove User from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('doctors_destroy')) {
            return abort(401);
        }
        User::deleteRecord1($id);
        return redirect()->route('admin.doctors.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('doctors_inactive')) {
            return abort(401);
        }
        User::inactiveRecord($id);

        return redirect()->route('admin.doctors.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('doctors_active')) {
            return abort(401);
        }
        User::activeRecord($id);

        return redirect()->route('admin.doctors.index');

    }

    /**
     * Display lcoation to add service for doctor.
     *
     * @param int $id
     */
    public function displaylocation($id)
    {
        if (!Gate::allows('doctors_allocate')) {
            return abort(401);
        }
        $doctor = User::find($id);

        $location = LocationsWidget::generateDropDownArray(session('account_id'));

        $doctor_has_location = DoctorHasLocations::where('user_id', '=', $doctor->id)->get();

        return view('admin.doctors.location', compact('doctor', 'location', 'doctor_has_location'));
    }

    /**
     * display services against location id.
     *
     * @param request
     */
    public function getservices(Request $request)
    {
        if (!Gate::allows('doctors_allocate')) {
            return abort(401);
        }

        $serive = ServiceWidget::generateServiceArrayArray($request, session('account_id'));

        $myarray = ['d' => $serive, 'locaiton_id_1' => $request->id];

        return response()->json($myarray);
    }

    /**
     * save services against location id.
     *
     * @param  $request
     */
    public function saveservices(Request $request)
    {
        if (!Gate::allows('doctors_allocate')) {
            return abort(401);
        }
        $myString = $request->id;
        $myArray = explode(',', $myString);
        $data = [];

        $data['user_id'] = $request->doctor_id;
        $data['location_id'] = $myArray[0];
        $data['service_id'] = $myArray[1];
        $service_endnode = Services::where('id', '=', $data['service_id'])->first();
        $data['end_node'] = $service_endnode->end_node;

        $checked = DoctorHasLocations::where([
            ['location_id', '=', $myArray[0]],
            ['service_id', '=', $myArray[1]],
            ['user_id', '=', $request->doctor_id]
        ])->get();

        if (count($checked) == '0') {

            $record = DoctorHasLocations::create($data);

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
    public function deleteservices(Request $request)
    {
        if (!Gate::allows('doctors_allocate')) {
            return abort(401);
        }


        DoctorHasLocations::find($request->id)->delete();
        AuditTrails::deleteEventLogger(DoctorHasLocations::$_table, 'delete', DoctorHasLocations::$_fillable, $request->id);
        return response()->json($request->id);

    }

    /**
     * Validate form fields
     */
    protected function verifyFields(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'phone' => 'required',
            'gender' => 'required',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
        ];

        $messages = [
            'name.required' => 'Name field is required',
            'email.required' => 'Email field is required',
            'phone.required' => 'Phone field is required',
            'password.required' => 'Password field is required',
            'password.min' => 'password must be at least 8 characters',
            'password.regex' => 'Password must be a combination of numbers, upper, lower, and special characters',
        ];
        return $validator = Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Validate create form fields
     */
    protected function verifyUpdateFields(Request $request, $id)
    {
        return $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'unique:users,email,' . $id,
            'phone' => 'required',
            'gender' => 'required',
        ]);
    }

    /**
     * Validate create form fields
     */
    protected function verifyPasswordFields(Request $request)
    {
        $rules = [
            'password' => 'required|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
        ];

        $messages = [
            'password.required' => 'Password field is required',
            'password.min' => 'password must be at least 8 characters',
            'password.regex' => 'Password must be a combination of numbers, upper, lower, and special characters',
        ];
        return $validator = Validator::make($request->all(), $rules, $messages);
    }
}
