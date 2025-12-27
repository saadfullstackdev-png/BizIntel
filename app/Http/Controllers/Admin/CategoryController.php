<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Models\Category;
use App\Models\Cities;
use App\Models\Regions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Auth;
use Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        if (! Gate::allows('categories_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'categories');

        return view('admin.category.index', compact('filters'));
    }

    /**
     * Display a listing of categories
     */
    public function datatable(Request $request)
    {
        $apply_filter = false;
        if($request->get('action')) {
            $action = $request->get('action');
            if(isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'categories');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $Categories = Category::getBulkData($request->get('id'));
            if($Categories) {
                foreach($Categories as $Category) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!Category::isChildExists($Category->id, Auth::User()->account_id)) {
                        $Category->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Category::getTotalRecords($request, Auth::User()->account_id,$apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $Categories = Category::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id,$apply_filter);

        if($Categories) {
            foreach($Categories as $category) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$category->id.'"/><span></span></label>',
                    'name' => $category->name,
                    'status' => view('admin.category.status', compact('category'))->render(),
                    'actions' => view('admin.category.actions', compact('category'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Create form
     */
    public function create()
    {
        if (! Gate::allows('categories_create')) {
            return abort(401);
        }

        return view('admin.category.create');
    }

    /**
     * store new categories
     */
    public function store(Request $request)
    {
        if (! Gate::allows('categories_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(Category::createRecord($request, Auth::User()->account_id)) {
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
     * Show the form for editing.
     */
    public function edit($id)
    {
        if (! Gate::allows('categories_edit')) {
            return abort(401);
        }

        $category = Category::getData($id);

        return view('admin.category.edit', compact('category'));
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('categories_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(Category::updateRecord($id, $request, Auth::User()->account_id)) {
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
     * Remove Permission from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('categories_destroy')) {
            return abort(401);
        }

        Category::DeleteRecord($id);

        return redirect()->route('admin.categories.index');

    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('categories_inactive')) {
            return abort(401);
        }
        Category::InactiveRecord($id);

        return redirect()->route('admin.categories.index');
    }

    /**
     * Inactive Record from storage.
     */
    public function active($id)
    {
        if (! Gate::allows('categories_inactive')) {
            return abort(401);
        }

        Category::activeRecord($id);

        return redirect()->route('admin.categories.index');
    }
}
