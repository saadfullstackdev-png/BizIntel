<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Http\Requests\Admin\FileUploadDiscountAllocationsRequest;
use App\Http\Requests\Admin\FileUploadTownRequest;
use App\Models\Cities;
use App\Models\DiscountAllocation;
use App\Models\Discounts;
use App\Models\Towns;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Validator;
use Carbon\Carbon;
use File;

class DiscountAllocationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Gate::allows('discountallocations_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'discountallocations');

        $users = User::where(['account_id' => Auth::User()->account_id])->whereNotIn('user_type_id', [3])->orwhere('is_celebrity','=' ,'1')->get()->pluck('name', 'id');
        $users->prepend('All', '');

        $discounts = Discounts::where('slug', '=', 'periodic')->get()->pluck('name', 'id');
        $discounts->prepend('All', '');

        return view('admin.discountallocations.index', compact('users', 'discounts', 'filters'));
    }

    /**
     * Show the form for creating a new discount allocation.
     */
    public function create()
    {
        if (! Gate::allows('discountallocations_create')) {
            return abort(401);
        }

        $users = User::where(['account_id' => Auth::User()->account_id])->whereNotIn('user_type_id', [3])->orwhere('is_celebrity','=' ,'1')->get()->pluck('name', 'id');
        $users->prepend('Select a User', '');

        $discounts = Discounts::where('slug', '=', 'periodic')->get()->pluck('name', 'id');
        $discounts->prepend('Select a Discount', '');

        return view('admin.discountallocations.create',compact( 'users','discounts'));
    }

    /**
     * Store a newly created .discount allocation
     */
    public function store(Request $request)
    {
        if (! Gate::allows('discountallocations_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $discount_allocation = DiscountAllocation::where([
            ['discount_id', '=', $request->discount_id],
            ['user_id', '=', $request->user_id],
            ['year', '=', Carbon::now()->format('Y')]
        ])->first();

        if($discount_allocation){
            return response()->json(array(
                'status' => 0,
                'message' => array('Discount Allocation already exist!'),
            ));
        } else {

            if(DiscountAllocation::createRecord($request, Auth::User()->account_id)) {

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
        }
    }

    /**
     * Display a listing of Discount Allocation.
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;
        if($request->get('action')) {
            $action = $request->get('action');
            if(isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'discountallocations');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $discount_allocations = DiscountAllocation::getBulkData($request->get('id'));
            if($discount_allocations) {
                foreach($discount_allocations as $discount_allocation) {
                    if(!DiscountAllocation::isChildExists($discount_allocation->id, Auth::User()->account_id)) {
                        $discount_allocation->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK";
            $records["customActionMessage"] = "Records has been deleted successfully!";
        }

        $iTotalRecords = DiscountAllocation::getTotalRecords($request, Auth::User()->account_id,$apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $discountallocations = DiscountAllocation::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if($discountallocations) {
            foreach($discountallocations as $discountallocation) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$discountallocation->id.'"/><span></span></label>',
                    'discount' => $discountallocation->discount->name,
                    'user' => $discountallocation->user->name,
                    'year' => $discountallocation->year,
                    'created_at' => Carbon::parse($discountallocation->created_at)->format('F j,Y h:i A'),
                    'status' => view('admin.discountallocations.status', compact('discountallocation'))->render(),
                    'actions' => view('admin.discountallocations.actions', compact('discountallocation'))->render(),
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
     */
    public function edit($id)
    {
        if (! Gate::allows('discountallocations_edit')) {
            return abort(401);
        }

        $discountallocation = DiscountAllocation::where([
            ['id', '=', $id],
            ['account_id', '=', Auth::User()->account_id]
        ])->first();

        if(!$discountallocation) {
            return view('error');
        }

        $users = User::where(['account_id' => Auth::User()->account_id])->whereNotIn('user_type_id', [3])->orwhere('is_celebrity','=' ,'1')->get()->pluck('name', 'id');
        $users->prepend('Select a User', '');

        $discounts = Discounts::where('slug', '=', 'periodic')->get()->pluck('name', 'id');
        $discounts->prepend('Select a Discount', '');

        return view('admin.discountallocations.edit', compact('users', 'discounts', 'discountallocation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('discountallocations_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $discount = DiscountAllocation::find($id);

        // Here we need to check discount name already exist except updated discount name
        $discount_info = DiscountAllocation::where([
            ['discount_id', '=', $request->discount_id],
            ['user_id', '=', $request->user_id],
            ['year', '=', $discount->year]
        ])->get()->except($id);

        if (count($discount_info)) {
            return response()->json(array(
                'status' => 0,
                'message' => array('Discount Allocation already exist!'),
            ));
        } else {
            if(DiscountAllocation::updateRecord($id, $request, Auth::User()->account_id)) {
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
        }
    }

    /**
     * Inactive Record from storage.
     */
    public function inactive($id)
    {
        if (! Gate::allows('discountallocations_inactive')) {
            return abort(401);
        }
        DiscountAllocation::InactiveRecord($id);

        return redirect()->route('admin.discountallocations.index');
    }

    /**
     * Inactive Record from storage.
     */
    public function active($id)
    {
        if (! Gate::allows('discountallocations_active')) {
            return abort(401);
        }

        DiscountAllocation::activeRecord($id);

        return redirect()->route('admin.discountallocations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (! Gate::allows('discountallocations_destroy')) {
            return abort(401);
        }

        DiscountAllocation::DeleteRecord($id);

        return redirect()->route('admin.discountallocations.index');

    }

    /**
     * Validate form fields
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'discount_id' => 'required',
            'user_id' => 'required'
        ]);
    }

    /**
     * Import discount allocation.
     *
     * @param Request $request
     */
    public function importdiscountallocation(Request $request)
    {
        if (!Gate::allows('discountallocations_import')) {
            flash('You are not authorized to access this resource.')->error()->important();
            return redirect()->route('admin.discountallocations.index');
        }

        return view('admin.discountallocations.import');
    }

    /**
     * Upload excel file.
     *
     * @param \App\Http\Requests\Admin\FileUploadLeadsRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function uploaddiscountallocation(FileUploadDiscountAllocationsRequest $request)
    {
        if (!Gate::allows('discountallocations_import')) {
            flash('You are not authorized to access this resource.')->error()->important();
            return redirect()->route('admin.discountallocations.index');
        }

        if ($request->hasfile('discountallocation_file')) {
            // Check if directory not exists then create it
            $dir = public_path('/discountallocations');
            if (!File::isDirectory($dir)) {
                // path does not exist so create directory
                File::makeDirectory($dir, 777, true, true);
                File::put($dir . '/index.html', 'Direct access is forbidden');
            }

            $File = $request->file('discountallocation_file');

            // Store File Information
            $name = str_replace('.' . $File->getClientOriginalExtension(), '', $File->getClientOriginalName());
            $ext = $File->getClientOriginalExtension();
            $full_name = $File->getClientOriginalName();
            $full_name_new = $name . '-' . rand(11111111, 99999999) . '.' . $ext;

            $File->move($dir, $full_name_new);

            // Read File and dump data
            $SpreadSheet = IOFactory::load($dir . DIRECTORY_SEPARATOR . $full_name_new);
            $SheetData = $SpreadSheet->getActiveSheet(0)->toArray(null, true, true, true);

            if (count($SheetData)) {
                if (
                    isset($SheetData[1])
                    && (
                        trim(strtolower($SheetData[1]['A'])) == 'employee' &&
                        trim(strtolower($SheetData[1]['B'])) == 'discount' &&
                        trim(strtolower($SheetData[1]['C'])) == 'year'
                    )) {

                    $allocationdata = array();
                    $count = 0;
                    $sucess = 0;
                    $fail = 0;
                    foreach ($SheetData as $SingleRow) {
                        if($count != 0){
                            if($request->is_celebrity && $request->is_celebrity == 1){
                                $user_info = User::where([
                                    ['name', '=', $SingleRow['A']],
                                    ['user_type_id', '=', '3'],
                                    ['is_celebrity', '=', '1']
                                ])->first();
                            } else {
                                $user_info = User::where([
                                    ['name', '=', $SingleRow['A']],
                                    ['user_type_id', '=', '2']
                                ])->first();
                            }
                            $discount_info = Discounts::where([
                                ['name', '=', $SingleRow['B']],
                                ['slug', '=', 'periodic'],
                                ['active', '=', 1]
                            ])->first();

                            if($user_info && $discount_info){

                                $discountAllocaiton = DiscountAllocation::where([
                                    ['user_id','=', $user_info->id],
                                    ['discount_id','=',$discount_info->id],
                                    ['year','=',$SingleRow['C']]
                                ])->first();

                                if(!$discountAllocaiton){
                                    $sucess++;
                                    $allocationdata[] = array(
                                        'discount_id' => $discount_info->id,
                                        'user_id' => $user_info->id,
                                        'year' => $SingleRow['C'],
                                        'account_id' => 1,
                                        'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()),
                                        'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())
                                    );
                                } else {
                                    $fail++;
                                }
                            } else {
                                $fail++;
                            }
                        }
                        $count++;
                    }

                    DiscountAllocation::insert($allocationdata);

                    flash($sucess.' Number of rows added and '.$fail.' fail')->success()->important();

                    return redirect()->route('admin.discountallocations.index');
                } else {
                    flash('Invalid data provided. Pattern should: name, city, active, account')->error()->important();
                }
            } else {
                flash('No input file specified..')->error()->important();
            }

            return redirect()->route('admin.discountallocations.import');
        }
    }

}
