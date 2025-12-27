<?php

namespace App\Console\Commands;

use App\Helpers\Invoice_Plan_Refund_Sms_Functions;
use App\Helpers\Payments\Meezan;
use App\Http\Controllers\Api\App\ApiHelpers\Apivalidation;
use App\Http\Controllers\Api\App\ApiHelpers\Package;
use App\Http\Controllers\Api\App\ApiHelpers\Plan;
use App\Http\Controllers\Api\App\ApiHelpers\WalletApi;
use App\Models\Appointments;
use App\Models\BundleHasServices;
use App\Models\Bundles;
use App\Models\DBBackups;
use App\Models\Leads;
use App\Models\Locations;
use App\Models\PackageAdvances;
use App\Models\PackageBundles;
use App\Models\Packages;
use App\Models\PackageSelling;
use App\Models\PackageSellingService;
use App\Models\PackageService;
use App\Models\Services;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\CardSubscription;
use App\Models\SubscriptionCharge;
use App\Models\WalletMeta;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Config;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\CardSubscriptionDetail;

class MeezanSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:meezan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the Wallet, package and plan';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $transactions = Transaction::where([
            ['status', '=', 'pending'],
            ['attempt', '<', 10]
        ])
            ->whereNotNull('order_id')
            ->get();

        if (count($transactions) > 0) {

            foreach ($transactions as $transaction) {

                $orderStatus = $this->updateOrderStatus($transaction->order_id, $transaction);

                $transaction->update(['attempt' => $transaction->attempt + 1]);

                if ($orderStatus['status'] && $orderStatus['status_code'] == 200 && $orderStatus['meezan_status'] == 'success') {
                    if ($transaction->paid_for == 'plan') {
                        if ($transaction->order_id && $transaction->paid_for_id) {
                            $this->plan($transaction);
                        } else {
                            $transaction->update(['message' => 'Transaction/Order id and paid for id not exists']);
                        }
                    }
                    if ($transaction->paid_for == 'package') {
                        if ($transaction->order_id && $transaction->paid_for_id) {
                            $this->package($transaction);
                        } else {
                            $transaction->update(['message' => 'Transaction/Order id and paid for id not exists']);
                        }
                    }
                    if ($transaction->paid_for == 'wallet') {
                        if ($transaction->order_id) {
                            $this->wallet($transaction);
                        } else {
                            $transaction->update(['message' => 'Transaction/Order id not exists']);
                        }
                    }if($transaction->paid_for == 'card_subscription'){
                        if ($transaction->order_id) {
                            $this->card_subscription($transaction);
                        } else {
                            $transaction->update(['message' => 'Transaction/Order id not exists']);
                        }
                    }if($transaction->paid_for == 'card_subscription'){
                        if ($transaction->order_id) {
                            $this->card_subscription($transaction);
                        } else {
                            $transaction->update(['message' => 'Transaction/Order id not exists']);
                        }
                    }
                } else {
                    $transaction->update(['message' => $orderStatus['message']]);
                }
            }
        }
    }

    /**
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
            return ['status' => false, 'message' => $errorMessage, 'data' => null, 'status_code' => 422];
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

        return ['status' => $status === 'success' ? true : false, 'message' => $orderStatusMessage, 'data' => null, 'status_code' => 200, 'meezan_status' => $status];

    }

    /**
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
     *
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

    /*
     *  Handle the wallet procedure
     */
    public function wallet($transaction)
    {
        // Some how one transaction able to enter multiple entries in wallet meta table that s why I apply that if clause
        $wallet_check = WalletMeta::where('transaction_id', '=', $transaction->id)->first();

        if (!$wallet_check) {
            $record = array(
                'payment_mode_id' => $transaction->payment_mode_id,
                'amount' => $transaction->amount,
                'transaction_id' => $transaction->id
            );

            $wallet = Wallet::where('patient_id', '=', $transaction->user_id)->first();

            DB::beginTransaction();

            try {
                if ($wallet) {

                    $transaction->update(['paid_for_id' => $wallet->id]);

                    $wallet_meta = WalletApi::saveWalletMeta($wallet, $record);

                    if ($wallet_meta) {
                        DB::commit();
                        $transaction->update(['message' => 'Wallet Topup successfully']);
                    } else {
                        DB::rollBack();
                        $transaction->update(['message' => 'Issue appear in wallet Meta']);
                    }
                } else {

                    $walletrecord = array(
                        'patient_id' => $transaction->user_id,
                        'account_id' => 1,
                    );
                    $result = Wallet::create($walletrecord);

                    $transaction->update(['paid_for_id' => $result->id]);

                    $wallet_meta = WalletApi::saveWalletMeta($result, $record);

                    if ($wallet_meta) {
                        DB::commit();
                        $transaction->update(['message' => 'Wallet Topup successfully']);
                    } else {
                        DB::rollBack();
                        $transaction->update(['message' => 'Issue appear in wallet Meta']);
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $transaction->update(['message' => 'Some error occurred! Please try again for wallet']);
            }
        }
    }

    /*
     * Handle the plan procedure
     */
    public function plan($transaction)
    {

        try {
            // Check if the entered amount is greater than total amount
            $package = Packages::whereActive(1)->wherePatient_id($transaction->user_id)->find($transaction->paid_for_id);

            if ($package) {

                $cash_receive = PackageAdvances::where([
                    ['package_id', '=', $package->id],
                    ['cash_flow', '=', 'in'],
                    ['is_cancel', '=', '0'],
                    ['active', '=', 1],
                ])->sum('cash_amount');

                if ($cash_receive >= $package->total_price) {

                    $transaction->update(['message' => 'You have already paid amount for this plan']);

                } else {

                    $balance_amount = $package->total_price - $cash_receive;

                    $amount = $transaction->amount;

                    if ($amount > $balance_amount) {

                        $transaction->update(['message' => 'Entered amount is more than balance for plan']);

                    } else {

                        DB::beginTransaction();

                        /*Save data in package advances*/
                        $data_packageAdvances['cash_flow'] = 'in';
                        $data_packageAdvances['cash_amount'] = $amount;
                        $data_packageAdvances['patient_id'] = $package->patient_id;
                        $data_packageAdvances['payment_mode_id'] = $transaction->payment_mode_id;
                        $data_packageAdvances['created_by'] = $transaction->user_id;
                        $data_packageAdvances['updated_by'] = $transaction->user_id;
                        $data_packageAdvances['package_id'] = $package->id;
                        $data_packageAdvances['location_id'] = $package->location_id;
                        $data_packageAdvances['transaction_id'] = $transaction->id;

                        $data_packageAdvances['account_id'] = 1;
                        /*End*/

                        $packageAdavances = PackageAdvances::createRecord($data_packageAdvances, $package);

                        if ($packageAdavances && isset($packageAdavances)) {
                            DB::commit();
                            $transaction->update(['message' => 'Your plan amount has been saved successfully !']);
                        }
                    }
                }
            } else {
                $transaction->update(['message' => 'Plan not found !']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $transaction->update(['message' => $e->getMessage()]);
        }
    }

    /*
     * Handle the package procedure
     */
    public function package($transaction)
    {
        DB::beginTransaction();

        try {

            $package = Bundles::where('id', '=', $transaction->paid_for_id)->first();
            $location = Locations::where('id', '=', $transaction->location_id)->first();

            $data = array();

            $data['bundle_id'] = $transaction->paid_for_id;
            $data['patient_id'] = $transaction->user_id;
            $data['location_id'] = $transaction->location_id;
            $data['name'] = $package->name;
            $data['actual_price'] = $package->services_price;
            $data['offered_price'] = $package->price;
            $data['total_services'] = $package->total_services;
            $data['apply_discount'] = $package->apply_discount;

            $calculation = Package::packagetaxcal($package->price, $location->tax_percentage, $package->tax_treatment_type_id);

            $data['is_exclusive'] = $calculation['is_exclusive'];
            $data['tax_exclusive_price'] = $calculation['price'];
            $data['tax_percentage'] = $location->tax_percentage;
            $data['tax_price'] = $calculation['tax'];
            $data['tax_including_price'] = $calculation['tax_amt'];

            $package_selling = PackageSelling::create($data);

            if ($package_selling) {

                $bundle_details = BundleHasServices::where('bundle_id', '=', $transaction->paid_for_id)->get();
                $data = array();

                foreach ($bundle_details as $detail) {

                    $calculation = Package::packagetaxcal($detail->calculated_price, $location->tax_percentage, $package->tax_treatment_type_id);
                    $service = Services::find($detail->service_id);
                    $data[] = array(
                        'package_selling_id' => $package_selling->id,
                        'bundle_id' => $transaction->paid_for_id,
                        'service_id' => $detail->service_id,
                        'autual_price' => $service->price,
                        'offered_price' => $detail->calculated_price,
                        'is_exclusive' => $calculation['is_exclusive'],
                        'tax_exclusive_price' => $calculation['price'],
                        'tax_percentage' => $location->tax_percentage,
                        'tax_price' => $calculation['tax'],
                        'tax_including_price' => $calculation['tax_amt']
                    );
                }
                $package_selling_service = PackageSellingService::insert($data);

                if ($package_selling_service) {

                    $this->saveplan($package_selling, $location, $transaction);

                    DB::commit();
                    $transaction->update(['message' => 'Package Purchase Successfully']);

                } else {
                    $transaction->update(['message' => 'Issue come while saving packge selling service']);
                }
            } else {
                $transaction->update(['message' => 'Issue come while saving packge selling']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $transaction->update(['message' => $e->getMessage()]);
        }
    }

    /*
     * Create the plan when package is sell
     */
    static function saveplan($package_selling, $location, $transaction)
    {

        $data = array();
        $data['random_id'] = md5(time() . rand(0001, 9999) . rand(78599, 99999));
        $data['sessioncount'] = 1;
        $data['total_price'] = $package_selling->offered_price;
        $data['is_exclusive'] = $package_selling->is_exclusive;
        $data['account_id'] = 1;
        $data['patient_id'] = $package_selling->patient_id;
        $data['location_id'] = $location->id;
        $data['bundle_id'] = $package_selling->bundle_id;
        $data['package_selling_id'] = $package_selling->id;

        $plan = Packages::create($data);
        $plan->update(['name' => sprintf('%05d', $plan->id)]);

        $data = array();

        $data['random_id'] = $plan->random_id;
        $data['is_allocate'] = 1;
        $data['qty'] = 1;
        $data['service_price'] = $package_selling->offered_price;
        $data['net_amount'] = $package_selling->offered_price;
        $data['is_exclusive'] = $package_selling->is_exclusive;
        $data['tax_exclusive_net_amount'] = $package_selling->tax_exclusive_price;
        $data['tax_percenatage'] = $package_selling->tax_percentage;
        $data['tax_price'] = $package_selling->tax_price;
        $data['tax_including_price'] = $package_selling->tax_including_price;
        $data['location_id'] = $location->id;
        $data['bundle_id'] = $package_selling->bundle_id;
        $data['package_id'] = $plan->id;

        $packagebundle = PackageBundles::create($data);

        $data = array();

        $package_selling_service = PackageSellingService::where('package_selling_id', '=', $package_selling->id)->get();

        foreach ($package_selling_service as $selling) {
            $data[] = array(
                'random_id' => $plan->random_id,
                'package_id' => $plan->id,
                'package_bundle_id' => $packagebundle->id,
                'service_id' => $selling->service_id,
                'package_selling_service_id' => $selling->id,
                'orignal_price' => $selling->offered_price,
                'price' => $selling->offered_price,
                'is_exclusive' => $selling->is_exclusive,
                'tax_exclusive_price' => $selling->tax_exclusive_price,
                'tax_percenatage' => $selling->tax_percentage,
                'tax_price' => $selling->tax_price,
                'tax_including_price' => $selling->tax_including_price,
            );
        }

        PackageService::insert($data);

        self::packageAdvance($plan, $location, $transaction);

        return true;
    }

    /*
     * Save the amount in package advance
     */
    static public function packageAdvance($plan, $location, $transaction)
    {
        $data = array();
        $data['cash_flow'] = 'in';
        $data['cash_amount'] = $transaction->amount;
        $data['account_id'] = 1;
        $data['patient_id'] = $plan->patient_id;
        $data['payment_mode_id'] = $transaction->payment_mode_id;
        $data['created_by'] = $transaction->user_id;
        $data['updated_by'] = $transaction->user_id;
        $data['package_id'] = $plan->id;
        $data['location_id'] = $location->id;
        $data['transaction_id'] = $transaction->id;

        $packageAdavances = PackageAdvances::create($data);

        /*Now sent message to user about cash received*/
        // First confirm does we need to send sms in cron job and second auth not present so we need to make new function
        // Invoice_Plan_Refund_Sms_Functions::PlanCashReceived_SMS($plan->id, $packageAdavances);

        return true;
    }
    public function card_subscription($transection){
        $date = new DateTime();
         if(SubscriptionCharge::first()->amount <= $transection->amount){
            $subscription= CardSubscription::where('patient_id',$transection->user_id)->first();
            if(!$subscription){
                CardSubscription::create([
                    'card_number' => round(microtime(true) * 1000).$transection->user_id,
                    'patient_id' => $transection->user_id,
                    'subscription_date' => date('Y-m-d H:i:s'),
                    'expiry_date' => $date->modify('+1 year')->format('Y-m-d H:i:s'),
                ]);
            }else{
                $subscription->update([
                    'subscription_date' => date('Y-m-d H:i:s'),
                    'expiry_date' => $date->modify('+1 year')->format('Y-m-d H:i:s'),
                    'is_active' => 1
                ]);
            }
            CardSubscriptionDetail::create([
                'subscription_card_id' => $subscription->id,
                'amount' =>  $transection->amount,
                'account_id' => $transection->user_id,
            ]);
         }
    }
}
