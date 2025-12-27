<?php

namespace App\Http\Controllers\Api\App\ApiHelpers;

use Illuminate\Support\Facades\Validator;

class Apivalidation
{
    /*
     * validation for package selling
     */
    static function purchasedservicesvalidation($request)
    {

            $rules = [
                'patient_id'  => 'required',
                'location_id' => 'required',
                'service_id'  => 'required',
                'price'       => 'required',
            ];
            // Define custom validation message for above validation
            $messages = [
                'patient_id.required' => 'Patient id is required',
                'location_id.required' => 'Location field is required',
                'service_id.required' => 'Service field is required',
                'price.required' => 'amount field is required',
            ];
       

        return Validator::make($request->all(), $rules, $messages);
    }
    static function packagesellingvalidation($request)
    {

        if ($request->wallet == 'false') {
            // First of all define validation rules
            $rules = [
                'transaction_id' => 'required',
                'location_id' => 'required',
                'package_id' => 'required',
                'amount' => 'required',
            ];
            // Define custom validation message for above validation
            $messages = [
                'transaction_id.required' => 'Transaction id is required',
                'location_id.required' => 'Location field is required',
                'package_id.required' => 'Package field is required',
                'amount.required' => 'amount field is required',
            ];
            // This can check validation and return new error message if found
        } else {
            // First of all define validation rules
            $rules = [
                'location_id' => 'required',
                'package_id' => 'required',
                'amount' => 'required',
            ];
            // Define custom validation message for above validation
            $messages = [
                'location_id.required' => 'Location field is required',
                'package_id.required' => 'Package field is required',
                'amount.required' => 'amount field is required',
            ];
            // This can check validation and return new error message if found
        }


        return Validator::make($request->all(), $rules, $messages);
    }

    /*
     * validation for add wallet amount
     */
    static function walletvalidation($request)
    {
        // First of all define validation rules
        $rules = [
            'transaction_id' => 'required',
        ];
        // Define custom validation message for above validation
        $messages = [
            'transaction_id.required' => 'Transaction id is required'
        ];
        // This can check validation and return new error message if found
        return Validator::make($request->all(), $rules, $messages);
    }

    /*
     * Validator for register order
     */
    static function register_order_validator($request)
    {
        if ($request->paid_for === 'package' ) {
            // First of all define validation rules
            $rules = [
                'payment_mode_id' => 'required',
                'amount' => 'required',
                'paid_for' => 'required',
                'paid_for_id' => 'required',
                'location_id' => 'required',
            ];
            // Define custom validation message for above validation
            $messages = [
                'payment_mode_id.required' => 'Payment mode is required',
                'amount.required' => 'Amount is required',
                'paid_for.required' => 'Paid for is required',
                'paid_for_id.required' => 'Paid for id is required',
                'location_id.required' => 'Location id is required',
            ];
        } else if ( $request->paid_for === 'wallet' ) {
            // First of all define validation rules
            $rules = [
                'payment_mode_id' => 'required',
                'amount' => 'required',
                'paid_for' => 'required'
            ];
            // Define custom validation message for above validation
            $messages = [
                'payment_mode_id.required' => 'Payment mode is required',
                'amount.required' => 'Amount is required',
                'paid_for.required' => 'Paid for is required'
            ];
        } else {
            // plan
            $rules = [
                'payment_mode_id' => 'required',
                'amount' => 'required',
                'paid_for' => 'required',
                'paid_for_id' => 'required',
            ];
            // Define custom validation message for above validation
            $messages = [
                'payment_mode_id.required' => 'Payment mode is required',
                'amount.required' => 'Amount is required',
                'paid_for.required' => 'Paid for is required',
                'paid_for_id.required' => 'Paid for id is required',
            ];
        }
        // This can check validation and return new error message if found
        return Validator::make($request->all(), $rules, $messages);
    }
}