<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\Payments\Meezan;
use App\Models\PaymentModes;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Controllers\Api\App\ApiHelpers\Apivalidation;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    /**
     * @param Request $request ( payment_mode_id, amount )
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function registerOrder(Request $request)
    {

//        location_id will be required only in case of package
        $validator = Apivalidation::register_order_validator($request);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->messages()->all(),
                'status_code' => 422,
            ]);
        }

        $paymentMode = PaymentModes::whereType('mobile')->whereActive(1)->get(['id','name'])->find($request->payment_mode_id);

        if ( is_null( $paymentMode ) ){
            return response([
                'success' => false,
                'message' => 'Payment mode not found',
                'data' => null,
                'status_code' => 422
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        switch ($paymentMode->name) {
            case 'Meezan':
                return Meezan::register_order($request, $paymentMode->id);
            default:
                break;
        }

        return null;
    }

    /**
     *
     * @param Request $request ( orderId )
     *
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function updateOrderStatus ( Request $request )
    {
        $validation = \Validator::make($request->all(), [
            'transaction_id' => 'required',
        ]);

        if ( $validation->fails() ){
            return \response([
                'success' => false,
                'message' => $validation->errors()->all(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY );
        }

        $transaction = Transaction::where('user_id', $request->user()->id )->find( $request->transaction_id );

        switch ($transaction->payment_mode->name){
            case 'Meezan':
                return Meezan::updateOrderStatus( $transaction->order_id, $transaction );
            default:
                return null;
        }
    }

    private function redirect_after_payment_process_validator( $request )
    {
        if ($request->paid_for === 'package' ) {
            // First of all define validation rules
            $rules = [
                'amount' => 'required',
                'paid_for' => 'required',
                'paid_for_id' => 'required',
                'location_id' => 'required',
            ];
            // Define custom validation message for above validation
            $messages = [
                'amount.required' => 'Amount is required',
                'paid_for.required' => 'Paid for is required',
                'paid_for_id.required' => 'Paid for id is required',
                'location_id.required' => 'Location id is required',
            ];
        } else if ($request->paid_for === 'wallet' || $request->paid_for === 'card_subscription') {
            // First of all define validation rules
            $rules = [
                'amount' => 'required',
                'paid_for' => 'required'
            ];
            // Define custom validation message for above validation
            $messages = [
                'amount.required' => 'Amount is required',
                'paid_for.required' => 'Paid for is required'
            ];
        } else {
            // plan
            $rules = [
                'amount' => 'required',
                'paid_for' => 'required',
                'paid_for_id' => 'required',
            ];
            // Define custom validation message for above validation
            $messages = [
                'amount.required' => 'Amount is required',
                'paid_for.required' => 'Paid for is required',
                'paid_for_id.required' => 'Paid for id is required',
            ];
        }
        // This can check validation and return new error message if found
        return Validator::make($request->all(), $rules, $messages);
    }

    public function redirect_after_payment_process( Request $request )
    {
        $validator = $this->redirect_after_payment_process_validator( $request );

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->messages()->all(),
                'status_code' => 422,
            ]);
        }

        if ( $request->paid_for === 'package' ){
            $url = "tdls://after-payment-process?transaction_id={$request->transaction_id}&paid_for={$request->paid_for}&location_id={$request->location_id}&paid_for_id={$request->paid_for_id}&amount={$request->amount}";
        } else if ( $request->paid_for === 'plan') {
            $url = "tdls://after-payment-process?transaction_id={$request->transaction_id}&paid_for={$request->paid_for}&paid_for_id={$request->paid_for_id}&amount={$request->amount}";
        } else {
            // wallet
            $url = "tdls://after-payment-process?transaction_id={$request->transaction_id}&paid_for={$request->paid_for}&amount={$request->amount}";
        }

        return view('api_force_redirect', compact( 'url'));
    }
}
