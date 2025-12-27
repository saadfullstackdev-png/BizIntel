<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Filters;
use App\Models\PackageSelling;
use App\Models\PackageSellingService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Auth;

class PackageSellingController extends Controller
{
    /**
     * Display a listing of the Package Selling.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('package_selling_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'packagesellings');

        if ($user_id = Filters::get(Auth::User()->id, 'packagesellings', 'patient_id')) {
            $patient = User::where(array(
                'id' => $user_id
            ))->first();
            if ($patient) {
                $patient = $patient->toArray();
            }
        } else {
            $patient = [];
        }

        return view('admin.packagesellings.index', compact('regions', 'filters', 'patient'));
    }

    /**
     * Display a listing of Package Selling.
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
                Filters::flush(Auth::User()->id, 'packagesellings');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        // Get Total Records
        $iTotalRecords = PackageSelling::getTotalRecords($request, $apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $packageSelling = PackageSelling::getRecords($request, $iDisplayStart, $iDisplayLength, $apply_filter);

        if ($packageSelling) {
            foreach ($packageSelling as $package) {

                $records["data"][] = array(
                    'id' => $package->id,
                    'name' => $package->name,
                    'patient' => $package->user->name,
                    'total_services' => $package->total_services,
                    'tax_including_price' => number_format($package->tax_including_price),
                    'refund' => $package->is_refund ? 'Yes' : 'No',
                    'created_at' => Carbon::parse($package->created_at)->format('F j,Y h:i A'),
                    'actions' => view('admin.packagesellings.actions', compact('package'))->render(),
                );
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show detail of package selling
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function display($id)
    {
        if (!Gate::allows('package_selling_manage')) {
            return abort(401);
        }
        $package = PackageSelling::findOrFail($id);

        if(!$package) {
            return view('error');
        }

        $package_selling_service = PackageSellingService::where('package_selling_id', '=',$package->id)->get();

        $records = array();

        foreach ($package_selling_service as $key => $selling){
            $records[$key] = array(
                'service' => $selling->service->name,
                'is_consumed' => $selling->is_consumed ? 'Yes' : 'No',
                'tax_including_price' => $selling->tax_including_price,
            );
        }

        return view('admin.packagesellings.display', compact('package', 'records'));
    }
}
