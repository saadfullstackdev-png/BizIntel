<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Filters;
use App\Models\PaymentModes;
use App\Models\Wallet;
use App\Models\WalletMeta;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Auth;
use Validator;

class WalletController extends Controller
{
    /**
     * Display a index page for datatable.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('wallets_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'wallets');

        if ($user_id = Filters::get(Auth::User()->id, 'wallets', 'patient_id')) {
            $patient = User::where(array(
                'id' => $user_id
            ))->first();
            if ($patient) {
                $patient = $patient->toArray();
            }
        } else {
            $patient = [];
        }

        return view('admin.wallets.index', compact( 'filters', 'patient'));

    }

    /**
     * Provide the data of Wallets.
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
                Filters::flush(Auth::User()->id, 'wallets');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        // Get Total Records
        $iTotalRecords = Wallet::getTotalRecords($request, $apply_filter);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $wallets = Wallet::getRecords($request, $iDisplayStart, $iDisplayLength, $apply_filter);

        if ($wallets) {
            foreach ($wallets as $wallet) {
                $wallet_in = WalletMeta::where([
                    ['cash_flow','=','in'],
                    ['cash_amount','>',0],
                    ['wallet_id','=',$wallet->id]
                ])->sum('cash_amount');

                $wallet_out = WalletMeta::where([
                    ['cash_flow','=','out'],
                    ['cash_amount','>',0],
                    ['wallet_id','=',$wallet->id]
                ])->sum('cash_amount');
                $records["data"][] = array(
                    'id' => $wallet->id,
                    'patient' => $wallet->user->name,
                    'balance' => $wallet_in - $wallet_out,
                    'created_at' => Carbon::parse($wallet->created_at)->format('F j,Y h:i A'),
                    'actions' => view('admin.wallets.actions', compact('wallet'))->render(),
                );
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * display the listing of meta of specific wallet.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function display($id)
    {
        if (!Gate::allows('wallets_manage')) {
            return abort(401);
        }

        $wallet = Wallet::find($id);
        $patient = User::find($wallet->patient_id);
        $filters = Filters::all(Auth::User()->id, 'walletmeta');


        return view('admin.wallets.display', compact('id', 'filters', 'patient', 'wallet'));
    }

    /**
     * Provide the meta data of specific wallet.
     *
     * @param \Illuminate\Http\Request
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function walletdatatable(Request $request, $id){

        $filname = 'walletmeta';
        $apply_filter = false;
        if($request->get('action')) {
            $action = $request->get('action');
            if(isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, $filname);
            } else if($action == 'filter') {
                $apply_filter = true;
            }
        }

        $records = array();
        $records["data"] = array();

        // Get Total Records
        $iTotalRecords = WalletMeta::getTotalRecords($request, Auth::User()->account_id, $id, $apply_filter, $filname);

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $walletmeta = WalletMeta::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $id, $apply_filter, $filname);

        if ($walletmeta) {

//            $balance = 0;

            $startOfDay = Carbon::now()->startOfDay()->toDateTimeString();
            $endOfDay = Carbon::now()->startOfDay()->addHours(23)->toDateTimeString();

            $payment_mode = PaymentModes::where([
                ['type', 'application'],
                ['name', '=', 'Cash']
            ])->first();

            foreach ($walletmeta as $advances) {

//                switch ($advances->cash_flow) {
//                    case 'in':
//                        $balance = $balance + $advances->cash_amount;
//                        break;
//                    case 'out':
//                        $balance = $balance - $advances->cash_amount;
//                        break;
//                    default:
//                        break;
//                }

                $is_refund = 'yes';

                if ($advances->cash_amount != 0) {

                    if ($advances->cash_flow == 'in') {
                        $cash_in = number_format($advances->cash_amount);
                        $cash_out = '-';
                        if ($advances->payment_mode_id != $payment_mode->id && !$advances->package_id) {
                            if ($startOfDay < $advances->created_at &&  $advances->created_at < $endOfDay) {
                                $is_refund = 'no';
                            }
                        } else {
                            $is_refund = 'zero';
                        }
                    } else {
                        $cash_out = number_format($advances->cash_amount);
                        $cash_in = '-';
                        $is_refund = 'zero';
                    }

                    if ($advances->is_refund_return == 1 || $advances->is_reverse_return == 1) {
                        $is_refund = 'zero';
                    }

                    $records["data"][] = array(
                        'patient' => $advances->user->name,
                        'phone' => \App\Helpers\GeneralFunctions::prepareNumber4Call($advances->user->phone),
                        'refund' => $advances->is_refund ? 'Yes' : 'NO',
                        'cash_in' => $cash_in,
                        'cash_out' => $cash_out,
//                        'balance' => number_format($balance),
                        'payment_mode' => $advances->payment_mode->name,
                        'is_refund' => $advances->is_refund_return == 1 ? 'Yes': '',
                        'is_reverse' => $advances->is_reverse_return == 1 ? 'Yes': '',
                        'created_at' => Carbon::parse($advances->created_at)->format('F j,Y h:i A'),
                        'actions' => view('admin.wallets.display_actions', compact('advances','is_refund'))->render(),
                    );
                } else {
                    $iTotalRecords--;
                }
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for create refund for wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function refund_create($id)
    {
        if (! Gate::allows('wallets_refund')) {
            return abort(401);
        }
        $wallet_in = WalletMeta::where([
            ['cash_flow','=','in'],
            ['cash_amount','>',0],
            ['wallet_id','=',$id]
        ])->sum('cash_amount');

        $wallet_out = WalletMeta::where([
            ['cash_flow','=','out'],
            ['cash_amount','>',0],
            ['wallet_id','=',$id]
        ])->sum('cash_amount');

        $refund_amount = $wallet_in - $wallet_out;

        return view('admin.wallets.refund', compact('id', 'refund_amount'));
    }

    /**
     * Store a amound in refund wallet.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function refund_store(Request $request)
    {
        if (! Gate::allows('wallets_refund')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(WalletMeta::createRefund($request, Auth::User()->account_id)) {

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
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'refund_amount' => 'required',
            'refund_note' => 'required',
        ]);
    }

    /**
     * Display the form to add amount direct in wallet
     */
    public function addcash($patient_id, $wallet_id) {
        if (! Gate::allows('wallets_addcash')) {
            return abort(401);
        }

        return view('admin.wallets.addcash',compact( 'patient_id', 'wallet_id'));
    }

    /**
     * Store the 
     */
    public function addcashstore (Request $request) {

        if (! Gate::allows('wallets_addcash')) {
            return abort(401);
        }

        $validator = $this->verifyAddCashFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(WalletMeta::addCashAmount($request, Auth::User()->account_id)) {

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
     * Validate add cash fields
     *
     * @param  \Illuminate\Http\Request $request
     */
    protected function verifyAddCashFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'patient_id' => 'required',
            'wallet_id' => 'required',
            'amount' => 'required',
        ]);
    }

    /**
     * Show the form for create bank refund for wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function refund_bank($id)
    {
        if (! Gate::allows('wallets_refund')) {
            return abort(401);
        }

        $wallet_meta = WalletMeta::find($id);

        $wallet_in = WalletMeta::where([
            ['cash_flow','=','in'],
            ['cash_amount','>',0],
            ['wallet_id','=',$wallet_meta->wallet_id]
        ])->sum('cash_amount');

        $wallet_out = WalletMeta::where([
            ['cash_flow','=','out'],
            ['cash_amount','>',0],
            ['wallet_id','=',$wallet_meta->wallet_id]
        ])->sum('cash_amount');

        $refund_amount = $wallet_in - $wallet_out;

        if ($wallet_meta->cash_amount <= $refund_amount) {
            $is_refund_allow = true;
        } else {
            $is_refund_allow = false;
        }

        return view('admin.wallets.refund_bank', compact('wallet_meta', 'refund_amount', 'is_refund_allow'));
    }

    /**
     * Refund through bank.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function refund_bank_store(Request $request)
    {
        if (! Gate::allows('wallets_refund')) {
            return abort(401);
        }

        $validator = $this->verifyrefundreversebankFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $refund = WalletMeta::createbankRefund($request, Auth::User()->account_id);

        if($refund['status'] && $refund['status_code'] == 200) {

            flash($refund['message'])->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => $refund['message'],
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => array($refund['message']),
            ));
        }
    }

    /**
     * Show the form for create refund for wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function reverse_bank($id)
    {
        if (! Gate::allows('wallets_reverse')) {
            return abort(401);
        }

        $wallet_meta = WalletMeta::find($id);

        $wallet_in = WalletMeta::where([
            ['cash_flow','=','in'],
            ['cash_amount','>',0],
            ['wallet_id','=',$wallet_meta->wallet_id]
        ])->sum('cash_amount');

        $wallet_out = WalletMeta::where([
            ['cash_flow','=','out'],
            ['cash_amount','>',0],
            ['wallet_id','=',$wallet_meta->wallet_id]
        ])->sum('cash_amount');

        $refund_amount = $wallet_in - $wallet_out;

        if ($wallet_meta->cash_amount <= $refund_amount) {
            $is_reverse_allow = true;
        } else {
            $is_reverse_allow = false;
        }

        return view('admin.wallets.reverse_bank', compact('wallet_meta', 'refund_amount', 'is_reverse_allow'));
    }

    /**
     * Reverse through bank.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function reverse_bank_store(Request $request)
    {
        if (! Gate::allows('wallets_reverse')) {
            return abort(401);
        }

        $validator = $this->verifyrefundreversebankFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $refund = WalletMeta::createbankReverse($request, Auth::User()->account_id);

        if($refund['status'] && $refund['status_code'] == 200) {

            flash($refund['message'])->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => $refund['message'],
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => array($refund['message']),
            ));
        }
    }

    /**
     * Validate bank refund/reverse fields
     *
     * @param  \Illuminate\Http\Request $request
     */
    protected function verifyrefundreversebankFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'wallet_id' => 'required',
            'wallet_meta_id' => 'required',
            'transaction_id' => 'required',
            'refund_note' => 'required',
            'refund_amount' => 'required',
        ]);
    }
}
