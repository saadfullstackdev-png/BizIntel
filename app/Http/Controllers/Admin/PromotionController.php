<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Models\Discounts;
use App\Models\Promotion;
use App\Models\Regions;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Auth;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('promotions_manage')) {
            return abort(401);
        }

        if ($user_id = Filters::get(Auth::User()->id, 'promotions', 'patient_id')) {
            $patient = User::where(array(
                'id' => $user_id
            ))->first();
            if ($patient) {
                $patient = $patient->toArray();
            }
        } else {
            $patient = [];
        }

        $filters = Filters::all(Auth::User()->id, 'promotions');

        $promotions = Discounts::where('slug','=','promotion')->get()->pluck('name', 'id');
        $promotions->prepend('All', '');

        return view('admin.promotions.index', compact('filters','promotions','patient'));
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
                Filters::flush(Auth::User()->id, 'promotions');
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        // Get Total Records
        $iTotalRecords = Promotion::getTotalRecords($request, Auth::User()->account_id, false, $apply_filter, 'promotions');

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $promotions = Promotion::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, false, $apply_filter, 'promotions');

        if($promotions) {
            foreach($promotions as $promotion) {
                $records["data"][] = array(
                    'user' => $promotion->user->name,
                    'discount' => $promotion->discount->name,
                    'code' => $promotion->code,
                    'use' => $promotion->use,
                    'taken' => $promotion->taken,
                    'actions' => view('admin.promotions.actions', compact('promotion'))->render(),
                );
            }
        }


        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('promotions_edit')) {
            return abort(401);
        }

        $promotion = Promotion::getData($id);

        if(!$promotion) {
            return view('error');
        }

        return view('admin.promotions.edit', compact('promotion'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('promotions_edit')) {
            return abort(401);
        }

        if(Promotion::updateRecord($id, $request, Auth::User()->account_id)) {
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
