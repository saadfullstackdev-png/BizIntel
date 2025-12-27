<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Filters;
use App\Models\PaymentModes;
use App\Models\Transaction;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        if (! Gate::allows('transactions_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'transactions');

        if ($user_id = Filters::get(Auth::User()->id, 'transactions', 'patient_id')) {
            $patient = User::where(array(
                'id' => $user_id
            ))->first();
            if ($patient) {
                $patient = $patient->toArray();
            }
        } else {
            $patient = [];
        }

        $payments = PaymentModes::where('type', '=', 'mobile')->get()->pluck('name','id');
        $payments->prepend('All', '');


        return view('admin.transactions.index', compact('filters', 'patient', 'payments'));
    }

    /**
     * Display a User As package in datatables.
     */
    public function datatable(Request $request)
    {
        $filename = 'transactions';
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

        // Get Total Records
        $iTotalRecords = Transaction::getTotalRecords($request, $apply_filter, $filename);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $transactions = Transaction::getRecords($request, $iDisplayStart, $iDisplayLength, $apply_filter, $filename);

        if ($transactions) {
            foreach ($transactions as $transaction) {
                if($transaction->paid_for == 'package'){
                    $paid_for_id = $transaction->bundle->name;
                } else if ($transaction->paid_for == 'plan') {
                    $paid_for_id = $transaction->paid_for_id;
                } else {
                    $paid_for_id = 'wallet';
                }
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $transaction->id . '"/><span></span></label>',
                    'patient_id' => $transaction->users->name,
                    'payment_mode_id' => $transaction->payment_mode->name,
                    'order_id' => $transaction->order_id,
                    'amount' => $transaction->amount,
                    'paid_for' => $transaction->paid_for,
                    'paid_for_id' => $paid_for_id,
                    'status' => $transaction->status,
                    'attempt' => $transaction->attempt,
                    'message' => $transaction->message,
                    'created_at' => Carbon::parse($transaction->created_at)->format('F j,Y h:i A'),
                    'actions' => '',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }
}
