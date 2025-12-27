<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\Payments\Meezan;
use App\Http\Controllers\Api\App\ApiHelpers\Apivalidation;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Http\Controllers\Controller;
use App\Models\WalletMeta;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\App\ApiHelpers\WalletApi;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /*
     * Add amount in wallet against patient
     */
    public function addamountwallet(Request $request)
    {
        $validator = Apivalidation::walletvalidation($request);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->messages()->all(),
                'status_code' => 422,
            ]);
        }

        $transaction = Transaction::where('user_id', $request->user()->id)->find($request->transaction_id);
//        dd($transaction);

        //$orderStatus = json_decode(Meezan::updateOrderStatus($request->order_id, $transaction), true);

        $orderStatus = Meezan::updateOrderStatus($transaction->order_id, $transaction);

        if ( !$orderStatus['status'] ){
            return response([
                'status' => false,
                'message' => 'Transaction was not made successfully',
                'data' => null,
                'status_code' => 422,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Some how one transaction able to enter multiple entries in wallet meta table that s why I apply that if clause
        $wallet_check = WalletMeta::where('transaction_id', '=', $transaction->id)->first();

        if (!$wallet_check) {
            $record = array(
                'payment_mode_id' => $transaction->payment_mode_id,
                'amount' => $transaction->amount,
                'transaction_id' => $transaction->id
            );

            $wallet = Wallet::where('patient_id', '=', Auth::User()->id)->first();

            DB::beginTransaction();

            try {
                if ($wallet) {

                    $transaction->update(['paid_for_id' => $wallet->id]);

                    $wallet_meta = WalletApi::saveWalletMeta($wallet, $record);

                    if ($wallet_meta) {
                        DB::commit();
                        return response([
                            'status' => true,
                            'message' => 'Amount successfully added',
                            'status_code' => 200,
                        ]);
                    } else {
                        DB::rollBack();
                        return response([
                            'status' => false,
                            'message' => 'Invalid Request',
                            'status_code' => 422,
                        ]);
                    }
                } else {

                    $walletrecord = array(
                        'patient_id' => Auth::User()->id,
                        'account_id' => 1,
                    );
                    $result = Wallet::create($walletrecord);

                    $transaction->update(['paid_for_id' => $result->id]);

                    $wallet_meta = WalletApi::saveWalletMeta($result, $record);

                    if ($wallet_meta) {
                        DB::commit();
                        return response([
                            'status' => true,
                            'message' => 'Amount successfully added',
                            'status_code' => 200,
                        ]);
                    } else {
                        DB::rollBack();
                        return response([
                            'status' => false,
                            'message' => 'Invalid Request',
                            'status_code' => 422,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response(['status' => false,
                    'message' => $e->getMessage(),
                    'status_code' => 422,]);
            }
        }
    }

    /*
     * Get wallet information against patient
     */
    public function getwallet(Request $request)
    {
        try{
            $walletinfo = Wallet::where('patient_id', '=', Auth::User()->id)->first();

            if(!$walletinfo){
                return response([
                    'status' => false,
                    'message' => 'Wallet not exists',
                    'status_code' => 400,
                ]);
            }

            $wallet_in = WalletMeta::where([
                ['cash_flow','=','in'],
                ['cash_amount','>',0],
                ['wallet_id','=',$walletinfo->id]
            ])->sum('cash_amount');

            $wallet_out = WalletMeta::where([
                ['cash_flow','=','out'],
                ['cash_amount','>',0],
                ['wallet_id','=',$walletinfo->id]
            ])->sum('cash_amount');

            $walletbalance = $wallet_in - $wallet_out;

            $walletmeta = WalletMeta::where('wallet_id', '=', $walletinfo->id)->get();

            $records = array();

            if ($walletmeta) {
                $balance = 0;
                foreach ($walletmeta as $advances) {

                    switch ($advances->cash_flow) {
                        case 'in':
                            $balance = $balance + $advances->cash_amount;
                            break;
                        case 'out':
                            $balance = $balance - $advances->cash_amount;
                            break;
                        default:
                            break;
                    }
                    if ($advances->cash_amount != 0) {

                        if ($advances->cash_flow == 'in') {
                            $cash_in = number_format($advances->cash_amount);
                            $cash_out = '-';

                        } else {
                            $cash_out = number_format($advances->cash_amount);
                            $cash_in = '-';
                        }
                        $records["data"][] = array(
                            'patient' => $advances->user->name,
                            'phone' => $advances->user->phone,
                            'refund' => $advances->is_refund ? 'Yes' : 'NO',
                            'cash_in' => $cash_in,
                            'cash_out' => $cash_out,
                            'balance' => number_format($balance),
                            'cash_amount' => '1',
                            'created_at' => Carbon::parse($advances->created_at)->format('F j,Y h:i A'),
                        );
                    }
                }
            }

            if($request->type == 'short'){
                return response([
                    'status' => true,
                    'message' => 'Data fetch successfully',
                    'data' => array('walletbalance' => $walletbalance),
                    'status_code' => 200,
                ]);
            } else {
                return response([
                    'status' => true,
                    'message' => 'Data fetch successfully',
                    'data' => array('walletbalance' => $walletbalance, 'history' => $records),
                    'status_code' => 200,
                ]);
            }
        } catch ( \Exception $e ) {
            return response([
                'status' => false,
                'message' => 'Something went wrong!',
                'status_code' => $e->getCode(),
            ]);
        }
    }
}
