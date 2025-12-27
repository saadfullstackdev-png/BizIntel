<?php

namespace App\Http\Controllers\Api\App\ApiHelpers;

use App\Models\WalletMeta;

class WalletApi
{
    /*
     * Save the wallet meta data
     */
    static function saveWalletMeta($result, $request)
    {
        $record = array(
            'cash_flow' => 'in',
            'cash_amount' => $request['amount'],
            'wallet_id' => $result->id,
            'patient_id' => $result->patient_id,
            'payment_mode_id' => $request['payment_mode_id'],
            'account_id' => 1,
            'transaction_id' => $request['transaction_id']
        );
        $data = WalletMeta::create($record);

        if ($data) {
            return true;
        } else {
            return false;
        }
    }
}