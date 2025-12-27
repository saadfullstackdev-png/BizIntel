<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Models\DiscountAllocation;
use App\Models\DiscountApproval;
use App\Models\Discounts;
use App\Models\Locations;
use App\Models\PackageAdvances;
use App\Models\PackageBundles;
use App\Models\Packages;
use App\Models\PackageSelling;
use App\Models\PackageService;
use App\Models\PaymentModes;
use App\Models\PlanApproval;
use App\Models\Services;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Auth;

class PlanApprovalController extends Controller
{
    /**
     * Load the index page.
     */
    public function index()
    {
        if (!Gate::allows('plans_manage')) {
            return abort(401);
        }
        $filters = Filters::all(Auth::User()->id, 'packages');

        if ($user_id = Filters::get(Auth::User()->id, 'packages', 'patient_id')) {
            $patient = User::where(array(
                'id' => $user_id
            ))->first();
            if ($patient) {
                $patient = $patient->toArray();
            }
        } else {
            $patient = [];
        }

        if ($package_id = Filters::get(Auth::User()->id, 'packages', 'package_id')) {
            $package = Packages::where(array(
                'id' => $package_id
            ))->first();
            if ($package) {
                $package = $package->toArray();
            }
        } else {
            $package = [];
        }

        if ($package_selling_id = Filters::get(Auth::User()->id, 'packages', 'package_selling_id')) {
            $packageselling = PackageSelling::where(array(
                'id' => $package_selling_id
            ))->first();
            if ($packageselling) {
                $packageselling = $packageselling->toArray();
            }
        } else {
            $packageselling = [];
        }

        $locations = Locations::getActiveSorted(ACL::getUserCentres(), 'full_address');
        $locations->prepend('All', '');

        return view('admin.planapprovals.index', compact('package', 'packageselling', 'locations', 'filters', 'patient'));
    }

    /**
     * Load the data for datatables.
     */
    public function datatable(Request $request)
    {
        $filename = 'packages';
        $apply_filter = false;
        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, $filename);
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $packages = Packages::getBulkData($request->get('id'));
            $any_deleted = false;
            if ($packages) {
                foreach ($packages as $package) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!Packages::isChildExists($package->id, Auth::User()->account_id)) {
                        $any_deleted = true;
                        $package->delete();
                    }
                }
            }
            if ($any_deleted) {
                $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
                $records["customActionMessage"] = "One or more record has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
            } else {
                $records["customActionStatus"] = "NO"; // pass custom message(useful for getting status of group actions)
                $records["customActionMessage"] = "Chalid records exist, unable to delete plan!"; // pass custom message(useful for getting status of group actions)
            }
        }

        $discount_allocation_ids = DiscountApproval::where('user_id', '=', Auth::User()->id)->groupBy('discount_id')->get()->pluck('discount_id')->toArray();

        // Get Total Records
        $iTotalRecords = PlanApproval::getTotalRecords($request, Auth::User()->account_id, false, $apply_filter, $filename, $discount_allocation_ids);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $packages = PlanApproval::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, false, $apply_filter, $filename, $discount_allocation_ids);

        if ($packages) {
            foreach ($packages as $package) {
                $session_count = count(PackageBundles::where('package_id', '=', $package->id)->get());
                /*We discuss in future what happen next*/
                $cash_receive = PackageAdvances::where([
                    ['package_id', '=', $package->id],
                    ['cash_flow', '=', 'in'],
                    ['is_cancel', '=', '0']
                ])->sum('cash_amount');
                if ($package->is_refund == '0') {
                    $refund_status = 'No';
                } else {
                    $refund_status = 'Yes';
                }
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $package->id . '"/><span></span></label>',
                    'name' => $package->user->name,
                    'package_id' => $package->name,
                    'location_id' => $package->location->city->name . "-" . $package->location->name,
                    'session_count' => $session_count,
                    'total' => number_format($package->total_price),
                    'cash_receive' => number_format($cash_receive),
                    'refund' => $refund_status,
                    'package_selling_id' => $package->package_selling_id,
                    'created_at' => Carbon::parse($package->created_at)->format('F j,Y h:i A'),
                    'status' => $package->active ? 'Active' : 'Inactive',
                    'actions' => view('admin.planapprovals.action', compact('package'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * display the package.
     */
    public function display($id)
    {
        if (!Gate::allows('plans_manage')) {
            return abort(401);
        }

        $package = Packages::find($id);

        $packagebundles = PackageBundles::where('package_id', '=', $package->id)->get();

        $packageservices = PackageService::where('package_id', '=', $package->id)->get();

        $packageadvances = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['is_cancel', '=', '0'],
            ['is_refund', '=', '0']
        ])->get();

        $cash_amount_in = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'in']
        ])->sum('cash_amount');

        $cash_amount_out = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'out']
        ])->sum('cash_amount');

        $cash_amount = $cash_amount_in - $cash_amount_out;

        /*We discuss it in future what happen next*/

        $grand_total = number_format($package->total_price - $cash_amount_in);

        $services = Services::getServices();
        $discount = Discounts::getDiscount(session('account_id'));
        $paymentmodes = PaymentModes::get()->pluck('name', 'id');
        $paymentmodes->prepend('Select Payment Mode', '');

        return view('admin.planapprovals.display', compact('package', 'packagebundles', 'packageservices', 'packageadvances', 'services', 'discount', 'paymentmodes', 'grand_total'));

    }

    /*
     * Approve the plan
     */
    public function approval($id)
    {

        $discount_allocation_ids = DiscountApproval::where('user_id', '=', Auth::User()->id)->groupBy('discount_id')->get()->pluck('discount_id')->toArray();

        PackageBundles::where('package_id', '=', $id)->whereIn('discount_id', $discount_allocation_ids)->update(['approved_by' => Auth::User()->id, 'is_hold' => 0]);

        $approved_count =  PackageBundles::where([
            ['package_id', '=', $id],
            ['is_hold','=', 1]
        ])->count();

        if($approved_count == 0){
            Packages::where('id','=',$id)->update(['is_hold' =>  0]);
        }

        return redirect()->route('admin.planapprovals.index');
    }
}
