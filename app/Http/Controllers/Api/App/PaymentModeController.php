<?php

namespace App\Http\Controllers\Api\App;

use App\Models\PaymentModes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentModeController extends Controller
{
    public function getpaymentmode()
    {
        try {

            $paymentModes = PaymentModes::whereType('mobile')->whereActive(1)->get(['id','name'])->toArray();

            if (count($paymentModes) > 0) {
                return response([
                    'success' => true,
                    'message' => "Payment Modes Data",
                    'data' => $paymentModes,
                    'status_code' => 200
                ], 200);
            }

            return response([
                'success' => false,
                'message' => "No Available Payment Mode found",
                'status_code' => 200
            ], 404);


        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'Some error occurred! Please try again !',
                'status_code' => 422,
            ]);
        }
    }
}
