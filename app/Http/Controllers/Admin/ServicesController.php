<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Filters;
use App\Helpers\GroupsTree;
use App\Helpers\NodesTree;
use App\Models\Services;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUpdateServicesRequest;
use DB;
use Auth;
use Validator;
use App\Models\TaxTreatmentType;

class ServicesController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!Gate::allows('services_manage')) {
            return abort(401);
        }


        /* Create Nodes with Parents */
        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);
        $Services = $parentGroups->nodeList;

        foreach ($Services as $id => &$data) {
            if ($id > 0) { // Skip invalid IDs
                $service = Services::with('category')->find($data['id']);
                $data['category_name'] = $service->category ? $service->category->name : null;
            }
        }
        return view('admin.services.index', compact('Services'));
    }

    /**
     * Store a newly created services and checked attribute exists or not.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
     * Show the form for creating new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('services_create')) {
            return abort(401);
        }

        $BaseServices = Services::getGroupsActiveOnly();

        if ($BaseServices) {
            $Services = GroupsTree::buildOptions(GroupsTree::buildTree($BaseServices->toArray()), 0);
        } else {
            $Services = array();
        }

        $service = new \stdClass();
        $service->duration = null;
        $service->parent_id = null;
        $service->image_src = null;

        $tax_treatment_types = TaxTreatmentType::get();

        $select_tax_treatment_type = 1;

        $consultancy_types = array(
            [
                'id' => 'both',
                'name' => 'Both'
            ],
            [
                'id' => 'in_person',
                'name' => 'In Person'
            ],
            [
                'id' => 'virtual',
                'name' => 'Virtual'
            ]
        );

        $select_consultancy_type = 'both';
        $categories = Category::pluck('name', 'id');
        $categories->prepend('Select Category', '');

        return view('admin.services.create', compact('Services', 'service', 'tax_treatment_types', 'select_tax_treatment_type', 'consultancy_types', 'select_consultancy_type', 'categories'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('services_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (Services::createRecord($request, Auth::User()->account_id)) {
            flash('Record has been created successfully.')->success()->important();

            return redirect()->route('admin.services.index');
        } else {
            flash('Something went wrong, please try again later.')->warning()->important();

            return redirect()->route('admin.services.index');
        }
    }

    /**
     * Validate form fields
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'name' => 'required',
            'parent_id' => 'required',
            // 'category_id' => 'required|exists:categories,id',
        ]);
    }

    /**
     * Show the form for editing Permission.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('services_edit')) {
            return abort(401);
        }
        $service = Services::findOrFail($id);

        $BaseServices = Services::getGroupsActiveOnly();

        if ($BaseServices) {
            $Services = GroupsTree::buildOptions(GroupsTree::buildTree($BaseServices->toArray(), 0, $service->id), $service->parent_id);
        } else {
            $Services = array();
        }

        $tax_treatment_types = TaxTreatmentType::get();

        if ($service->tax_treatment_type_id == 0) {
            $select_tax_treatment_type = 1;
        } else {
            $select_tax_treatment_type = $service->tax_treatment_type_id;
        }

        $consultancy_types = array(
            [
                'id' => 'both',
                'name' => 'Both'
            ],
            [
                'id' => 'in_person',
                'name' => 'In Person'
            ],
            [
                'id' => 'virtual',
                'name' => 'Virtual'
            ]
        );

        $select_consultancy_type = $service->consultancy_type;
        $categories = Category::pluck('name', 'id');
        $categories->prepend('All', '');

        return view('admin.services.edit', compact('service', 'Services', 'tax_treatment_types', 'select_tax_treatment_type', 'consultancy_types','select_consultancy_type', 'categories'));
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
        if (!Gate::allows('services_edit')) {
            return abort(401);
        }
        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $service = Services::findOrFail($id);

        if (
            Services::isChildExists($id, Auth::User()->account_id) &&
            ($service->parent_id != $request->get('parent_id') || $service->end_node != $request->get('end_node'))
        ) {
            flash('Parent Service can not be changed due to one or more services are associated with it.')->warning()->important();
            return redirect()->route('admin.services.index');
        }

        if (Services::updateRecord($id, $request, Auth::User()->account_id)) {
            flash('Record has been updated successfully.')->success()->important();

            return redirect()->route('admin.services.index');
        } else {
            flash('Something went wrong, please try again later.')->warning()->important();

            return redirect()->route('admin.services.index');
        }
    }

    /**
     * Remove Permission from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('services_destroy')) {
            return abort(401);
        }
        Services::deleteRecord($id);

        return redirect()->route('admin.services.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('services_inactive')) {
            return abort(401);
        }

        Services::inactiveRecord($id);

        return redirect()->route('admin.services.index');

    }

    /**
     * Inactive Record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('services_active')) {
            return abort(401);
        }
        Services::activeRecord($id);

        return redirect()->route('admin.services.index');
    }

}
