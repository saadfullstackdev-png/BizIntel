<?php


namespace App\Helpers\Payments;


use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Meezan
{

    /**
     * @param int $code
     * @return string|null
     */
    public static function errorMessageRegister(int $code = 0): ?string
    {
        $errorCodes = [
            0 => 'No System Error',
            1 => 'Order with this number was registered, but was not paid',
            3 => 'Unknown currency.',
            4 => 'Order number is not specified.',
            5 => 'Incorrect value of a request parameter.',
            7 => 'System error.',
            14 => 'PaymentWay is invalid',
        ];

        if ($code > 0 && array_key_exists($code, $errorCodes)) {
            return $errorCodes[$code];
        }

        return null;
    }

    /**
     * Register the order for wallet, plan and package
     * @param Request $request ( amount )
     * @param $payment_mode_id
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function register_order(Request $request, $payment_mode_id)
    {

        $transaction = array(
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'payment_mode_id' => $payment_mode_id,
            'paid_for' => $request->paid_for,
            'paid_for_id' => $request->paid_for_id,
            'location_id' => $request->location_id,
            'status' => 'pending'
        );

        if (!$newTransaction = Transaction::create($transaction)) {
            return \response([
                'status' => false,
                'message' => 'Payment could not be made, Please try again !',
                'data' => null,
                'status_code' => 422
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $client = new Client();

        if ( $request->paid_for === 'package'){
            $returnUrl = config('payments.meezan.returnUrl').''."?transaction_id={$newTransaction->id}&paid_for={$request->paid_for}&location_id={$request->location_id}&paid_for_id={$request->paid_for_id}&amount={$request->amount}";
        } else if ( $request->paid_for === 'plan') {
            $returnUrl = config('payments.meezan.returnUrl').''."?transaction_id={$newTransaction->id}&paid_for={$request->paid_for}&paid_for_id={$request->paid_for_id}&amount={$request->amount}";
        } else {
            // wallet 
            //card_subscription
            $returnUrl = config('payments.meezan.returnUrl')."?transaction_id=".$newTransaction->id."&paid_for=".$request->paid_for."&amount=".$request->amount;
        }

        $data = [
            'query' => [
                'userName' => config('payments.meezan.userName'),
                'password' => config('payments.meezan.password'),
                'returnUrl' => strip_tags($returnUrl),
                'orderNumber' => $newTransaction->id,
                'amount' => $request->amount*100,
            ],
        ];

        $response = $client->request('GET', 'https://acquiring.meezanbank.com/payment/rest/register.do', $data);

        $response_array = json_decode($response->getBody()->getContents(), true);

        $errorMessage = static::errorMessageRegister($response_array['errorCode']);


        if (is_null($errorMessage)) {

            $newTransaction->update(['order_id' => $response_array['orderId']]);

            return response([
                'status' => true,
                'message' => "Order has been registered !",
                'data' => $response_array,
                'status_code' => 200
            ], Response::HTTP_OK);
        }

        return response([
            'status' => false,
            'message' => $errorMessage,
            'data' => null,
            'status_code' => 422
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * order Status for order
     * @param int $OrderStatusCode
     * @return string|null
     */
    public static function OrderStatusMessage(int $OrderStatusCode): ?string
    {

        $OrderStatusCodes = [
            0 => 'Order registered, but not paid',
            1 => 'Transaction has been approved (for a one-phase payment)',
            2 => 'Amount was deposited successfully',
            3 => 'Authorization has been reversed',
            4 => 'Transaction has been refunded',
            6 => 'Authorization is declined',
        ];

        if ($OrderStatusCode && array_key_exists($OrderStatusCode, $OrderStatusCodes)) {
            return $OrderStatusCodes[$OrderStatusCode];
        }

        return null;
    }

    /**
     * Error order status
     * @param int $errorCode
     * @return string|null
     */
    public static function errorCodeOrderStatus(int $errorCode): ?string
    {

        $errorCodesOrderStatus = array(
            0 => 'success',
            2 => 'The order is declined because of an error in the payment credentials.',
            5 => 'Access is denied',
            6 => 'orderId is empty',
            7 => 'System Error',
        );

        if ($errorCode > 0 && array_key_exists($errorCode, $errorCodesOrderStatus)) {
            return $errorCodesOrderStatus[$errorCode];
        }

        return null;

    }

    /**
     * Update the order status
     * @param string $orderId
     * @param Transaction $transaction
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function updateOrderStatus(string $orderId, Transaction $transaction)
    {

        $client = new Client();
        $data = [
            'query' => [
                'userName' => config('payments.meezan.userName'),
                'password' => config('payments.meezan.password'),
                'orderId' => $orderId,
            ],
        ];

        $response = $client->request('GET', 'https://acquiring.meezanbank.com/payment/rest/getOrderStatus.do', $data);

        $response_array = json_decode($response->getBody()->getContents(), true);

        $errorMessage = static::errorCodeOrderStatus($response_array['ErrorCode']);

        if (!is_null($errorMessage)) {
            $transaction->update(['status' => 'cancelled']);
            return [
                'status' => false,
                'message' => $errorMessage,
                'data' => null,
                'status_code' => 422
            ];
        }

        $orderStatusMessage = static::OrderStatusMessage($response_array['OrderStatus']);

        $status = 'pending';
        switch ( $response_array['OrderStatus'] ){
            case 2:
                $status = 'success';
                break;
            case 3:
            case 4:
            case 6:
                $status = 'cancelled';
                break;
        }

        $transaction->update(['status' => $status]);

        return ['status' => $status === 'success' ? true : false, 'message' => $orderStatusMessage, 'data' => null, 'status_code' => 200];

//        return \response([
//            'status' => $status === 'success' ? true : false,
//            'message' => $orderStatusMessage,
//            'data' => null,
//            'status_code' => 200,
//        ], Response::HTTP_OK );

    }

    /**
     * Refund from meezan
     */
    public static function meezan_refund($request, $transaction) {

        $data = [
            'query' => [
                'userName' => config('payments.meezan.userName'),
                'password' => config('payments.meezan.password'),
                'amount' => $request->get('refund_amount')*100,
                'orderId' => $transaction->order_id,
            ],
        ];

        $client = new Client();

        $response = $client->request('GET', 'https://acquiring.meezanbank.com/payment/rest/refund.do', $data);

        $response_array = json_decode($response->getBody()->getContents(), true);

        $errorMessage = static::errorMessageRegisterForBankReverseRefund($response_array['errorCode']);

        if (is_null($errorMessage)) {

            return [
                'status' => true,
                'message' => "Refund Through Bank Successfully Done!",
                'status_code' => 200
            ];
        }

        return [
            'status' => false,
            'message' => $errorMessage,
            'status_code' => 422
        ];
    }

    /**
     * Reverse from meezan
     */
    public static function meezan_reverse($request, $transaction) {

        $data = [
            'query' => [
                'userName' => config('payments.meezan.userName'),
                'password' => config('payments.meezan.password'),
                'orderId' => $transaction->order_id,
            ],
        ];

        $client = new Client();

        $response = $client->request('GET', 'https://acquiring.meezanbank.com/payment/rest/reverse.do', $data);

        $response_array = json_decode($response->getBody()->getContents(), true);

        $errorMessage = static::errorMessageRegisterForBankReverseRefund($response_array['errorCode']);

        if (is_null($errorMessage)) {

            return [
                'status' => true,
                'message' => "Reverse Through Bank Successfully Done!",
                'status_code' => 200
            ];
        }

        return [
            'status' => false,
            'message' => $errorMessage,
            'status_code' => 422
        ];
    }

    /**
     * Error Messages for refund bank
     * @param int $code
     * @return string|null
     */
    public static function errorMessageRegisterForBankReverseRefund(int $code = 0): ?string
    {
        $errorCodes = [
            0 => 'No System Error',
            5 => 'Invalid amoun.',
            6 => 'Unregistered OrderId.',
            7 => 'Payment must be in a correct state',
        ];

        if ($code > 0 && array_key_exists($code, $errorCodes)) {
            return $errorCodes[$code];
        }

        return null;
    }

}