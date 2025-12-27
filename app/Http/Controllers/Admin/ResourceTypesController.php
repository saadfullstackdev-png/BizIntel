<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use DB;
use App\Models\ResourceTypes;
use Session;
use Auth;
use Validator;

class ResourceTypesController extends Controller
{
    /**
     * Display a Home Page or index page function.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Gate::allows('resource_types_manage')){
            return abort(401);
        }
        return view('admin.resource_types.index');
    }
    /**
     * show the page of create resource types and Model.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!Gate::allows('resource_types_create')){
            return abort(401);
        }
        return view('admin.resource_types.create');
    }

    /**
     * Store a newly created resource types in storage/database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Gate::allows('resource_types_create')){
            return abort('401');
        }

        if (ResourceTypes::createRecord($request)) {
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
        ]);
    }

    /**
     * Display code for datatable of resource type
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $resource_types = ResourceTypes::whereIn('id', $request->get('id'));
            if ($resource_types) {
                $resource_types->delete();
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = ResourceTypes::getTotalRecords($request);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $records = array();
        $records["data"] = array();

        $resource_types = ResourceTypes::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($resource_types) {
            foreach($resource_types as $resource_type) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$resource_type->id.'"/><span></span></label>',
                    'name' => $resource_type->name,
                    'actions' => view('admin.resource_types.actions', compact('resource_type'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Display edit model for edit resource type.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!Gate::allows('resource_types_edit')){
            return abort('401');
        }
        $resource_types = ResourceTypes::findOrFail($id);

        return view('admin.resource_types.edit', compact('resource_types'));

    }

    /**
     * Update the specified resource type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(! Gate::allows('resource_types_edit')){
            return abort ('401');
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (ResourceTypes::updateRecord($id, $request, Auth::User()->account_id)) {
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

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('resource_types_inactive')) {
            return abort(401);
        }
        ResourceTypes::inactiveRecord($id);

        return redirect()->route('admin.resource_types.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('resource_types_active')) {
            return abort(401);
        }

        ResourceTypes::activeRecord($id);

        return redirect()->route('admin.resource_types.index');
    }


    /**
     * Remove or delete the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('resource_types_destroy')) {
            return abort(401);
        }
        ResourceTypes::deleteRecord($id);

        return redirect()->route('admin.resource_types.index');
    }


}
