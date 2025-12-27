<?php

namespace App\Models;

use App\Helpers\Filters;
use App\Helpers\Payments\Meezan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\sendEmail;

class WalletMeta extends Model
{
    protected $fillable = ['cash_flow', 'cash_amount', 'is_refund', 'refund_note', 'wallet_id', 'patient_id', 'payment_mode_id', 'account_id', 'transaction_id', 'is_refund_return', 'is_reverse_return', 'wallet_meta_id', 'package_id', 'created_at', 'updated_at'];
    protected $table = 'wallet_metas';

    /**
     * get the user
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Patients', 'patient_id')->withTrashed();
    }

    /**
     * Get the payment mode.
     */
    public function payment_mode()
    {
        return $this->belongsTo('App\Models\PaymentModes')->withTrashed();
    }

    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id
     * @param (int) $id
     * @param (boolean) $apply_filter
     * @param (string) $filename
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $account_id, $id, $apply_filter = false, $filename)
    {
        $where = self::filters_walletmeta($request, $account_id, $id, $apply_filter, $filename);

        if (count($where)) {
            return self::where($where)->count();
        } else {
            return self::count();
        }
    }

    /**
     * Get Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $iDisplayStart Start Index
     * @param (int) $iDisplayLength Total Records Length
     * @param (int) $account_id
     * @param (int) $id
     * @param (boolean) $apply_filter
     * @param (string) $filename
     *
     * @return (mixed)
     */
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id, $id, $apply_filter = false, $filename)
    {

        $where = self::filters_walletmeta($request, $account_id, $id, $apply_filter, $filename);
        if (count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->get();
        }
    }

    /**
     * Filter for wallet meta
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id
     * @param (int) $id
     * @param (boolean) $apply_filter
     * @param (string) $filename
     */
    static public function filters_walletmeta($request, $account_id, $id, $apply_filter = false, $filename)
    {
        $where = array();

        if ($id) {
            $where[] = array(
                'wallet_id',
                '=',
                $id
            );
            Filters::put(Auth::user()->id, $filename, 'id', $id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, $filename, 'id');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'id')) {
                    $where[] = array(
                        'patient_id',
                        '=',
                        Filters::get(Auth::user()->id, $filename, 'id')
                    );
                }
            }
        }

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::user()->id, $filename, 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, $filename, 'account_id');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::user()->id, $filename, 'account_id')
                    );
                }
            }
        }

        if ($request->get('created_from') && $request->get('created_from') != '') {
            $where[] = array(
                'created_at',
                '>=',
                $request->get('created_from') . ' 00:00:00'
            );
            Filters::put(Auth::User()->id, $filename, 'created_from', $request->get('created_from') . ' 00:00:00');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'created_from');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'created_from')) {
                    $where[] = array(
                        'created_at',
                        '>=',
                        Filters::get(Auth::User()->id, $filename, 'created_from')
                    );
                }
            }
        }

        if ($request->get('created_to') != '') {
            $where[] = array(
                'created_at',
                '<=',
                $request->get('created_to') . ' 23:59:59'
            );
            Filters::put(Auth::User()->id, $filename, 'created_to', $request->get('created_to') . ' 23:59:59');
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'created_to');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'created_to')) {
                    $where[] = array(
                        'created_at',
                        '<=',
                        Filters::get(Auth::User()->id, $filename, 'created_to')
                    );
                }
            }
        }
        return $where;
    }

    /**
     * Create direct refund
     */
    static public function createRefund($request, $account_id)
    {

        $wallet_info = Wallet::find($request->wallet_id);

        $wallet_in = WalletMeta::where([
            ['cash_flow', '=', 'in'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $request->wallet_id]
        ])->sum('cash_amount');

        $wallet_out = WalletMeta::where([
            ['cash_flow', '=', 'out'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $request->wallet_id]
        ])->sum('cash_amount');

        $refund_amount = $wallet_in - $wallet_out;

        if ($refund_amount >= $request->refund_amount) {

            // Set Account ID
            $data['cash_flow'] = 'out';
            $data['cash_amount'] = $request->get('refund_amount');
            $data['is_refund'] = '1';
            $data['refund_note'] = $request->refund_note;
            $data['wallet_id'] = $request->wallet_id;
            $data['patient_id'] = $wallet_info->patient_id;
            $data['payment_mode_id'] = '1';
            $data['account_id'] = $account_id;
            $record = self::create($data);
            self::sendRefundEmail(Auth::User()->name, $wallet_info->user->name, $request->get('refund_amount'), $request->refund_note, 'Direct');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add Direct Cash
     */
    static public function addCashAmount($request, $account_id)
    {

        $payment_mode = PaymentModes::where([
            ['type', 'application'],
            ['name', '=', 'Cash']
        ])->first();

        $record = array(
            'cash_flow' => 'in',
            'cash_amount' => $request['amount'],
            'wallet_id' => $request->wallet_id,
            'patient_id' => $request->patient_id,
            'payment_mode_id' => $payment_mode ? $payment_mode->id : 1,
            'account_id' => $account_id,
            'transaction_id' => $request['transaction_id']
        );

        $data = WalletMeta::create($record);

        if ($data) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create direct Bank refund
     */
    static public function createbankRefund($request, $account_id)
    {

        $wallet_info = Wallet::find($request->wallet_id);

        $wallet_in = WalletMeta::where([
            ['cash_flow', '=', 'in'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $request->wallet_id]
        ])->sum('cash_amount');

        $wallet_out = WalletMeta::where([
            ['cash_flow', '=', 'out'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $request->wallet_id]
        ])->sum('cash_amount');

        $refund_amount = $wallet_in - $wallet_out;

        if ($refund_amount >= $request->refund_amount) {

            DB::beginTransaction();

            // Set Account ID
            $data['cash_flow'] = 'out';
            $data['cash_amount'] = $request->get('refund_amount');
            $data['is_refund'] = '1';
            $data['refund_note'] = $request->refund_note;
            $data['wallet_id'] = $request->wallet_id;
            $data['patient_id'] = $wallet_info->patient_id;
            $data['payment_mode_id'] = '6';
            $data['account_id'] = $account_id;
            $data['wallet_meta_id'] = $request->wallet_meta_id;
            $record = self::create($data);

            if ($record) {
                $transaction = Transaction::find($request->transaction_id);

                $refund = Meezan::meezan_refund($request, $transaction);

                if ($refund['status'] && $refund['status_code'] == 200) {
                    self::where('id', '=', $request->wallet_meta_id)->update(['is_refund_return' => 1]);
                    self::sendRefundEmail(Auth::User()->name, $wallet_info->user->name, $request->get('refund_amount'), $request->refund_note, 'Through Refund');
                    DB::commit();
                } else {
                    DB::rollBack();
                }
            }
            return [
                'status' => $refund['status'],
                'message' => $refund['message'],
                'status_code' => $refund['status_code']
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Amount greater than balance'
            ];
        }
    }

    /**
     * Create direct Bank reverse
     */
    static public function createbankReverse($request, $account_id)
    {

        $wallet_info = Wallet::find($request->wallet_id);

        $wallet_in = WalletMeta::where([
            ['cash_flow', '=', 'in'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $request->wallet_id]
        ])->sum('cash_amount');

        $wallet_out = WalletMeta::where([
            ['cash_flow', '=', 'out'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $request->wallet_id]
        ])->sum('cash_amount');

        $refund_amount = $wallet_in - $wallet_out;

        if ($refund_amount >= $request->refund_amount) {

            DB::beginTransaction();

            // Set Account ID
            $data['cash_flow'] = 'out';
            $data['cash_amount'] = $request->get('refund_amount');
            $data['is_refund'] = '1';
            $data['refund_note'] = $request->refund_note;
            $data['wallet_id'] = $request->wallet_id;
            $data['patient_id'] = $wallet_info->patient_id;
            $data['payment_mode_id'] = '6';
            $data['account_id'] = $account_id;
            $data['wallet_meta_id'] = $request->wallet_meta_id;
            $record = self::create($data);

            if ($record) {
                $transaction = Transaction::find($request->transaction_id);

                $refund = Meezan::meezan_reverse($request, $transaction);

                if ($refund['status'] && $refund['status_code'] == 200) {
                    self::where('id', '=', $request->wallet_meta_id)->update(['is_reverse_return' => 1]);
                    self::sendRefundEmail(Auth::User()->name, $wallet_info->user->name, $request->get('refund_amount'), $request->refund_note, 'Through Reverse');
                    DB::commit();
                } else {
                    DB::rollBack();
                }
            }
            return [
                'status' => $refund['status'],
                'message' => $refund['message'],
                'status_code' => $refund['status_code']
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Amount greater than balance',
            ];
        }
    }

    static public function sendRefundEmail($creator, $receiver, $amount, $reason, $refundType)
    {
        $message = "Dear Accountant,<br/><br/>A refund is triggered with the following information:<br/><br/><b>Creator:</b> $creator<br/><b>Receiver:</b> $receiver<br/><b>Amount:</b> $amount<br/><b>Refund type:</b> $refundType<br/><b>Reason:</b> $reason<br/><b>Time:</b> " . carbon::now()->format('d F Y H:i:s') . "<br/><br/><b>Thank you<b>";
        $status = sendEmail::sendEmail(env('ACCOUNTEMAIL'), 'Refund Request', $message);
        return true;
    }
}
