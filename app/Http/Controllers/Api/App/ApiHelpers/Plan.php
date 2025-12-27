<?php

namespace App\Http\Controllers\Api\App\ApiHelpers;

use App\Helpers\Invoice_Plan_Refund_Sms_Functions;
use App\Models\Bundles;
use App\Models\PackageAdvances;
use App\Models\PackageBundles;
use App\Models\Packages;
use App\Models\PackageService;
use App\Models\PackageSellingService;
use App\Models\WalletMeta;
use Carbon\Carbon;
use Auth;

class Plan
{
    /*
     * That function give bundle and their services information against plan number
     */
    static function getplantreatmentdetail($id)
    {
        $bundles = array();
        $packagebundles = PackageBundles::where('package_id', '=', $id)->get();
        // Getting count of all services for status
        $total_count = PackageService::where('package_id', '=', $id)->count();
        $consumed = 0;
        $status = '';
        foreach ($packagebundles as $bundlekey => $packagebundle) {
            $bundle_info = Bundles::where('id','=',$packagebundle->bundle_id)->first();
            if($bundle_info->type == 'single'){
                $firstservicesinfo = PackageService::where([['package_id', '=', $id], ['package_bundle_id', '=', $packagebundle->id]])->first();
                $image_src = $firstservicesinfo->service->image_src ? '/service_images/' . $firstservicesinfo->service->image_src : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg';
            } else {
                $image_src = $bundle_info->image_src ? '/bundle_images/' . $bundle_info->image_src : '/default_image/ae877a93f4983269a8c9520ad011a46f.png';
            }
            $bundles[$bundlekey] = array(
                'id' => $packagebundle->id,
                'name' => $packagebundle->bundle->name,
                'image_src' => $image_src,
                'service_price' => $packagebundle->service_price,
                'discount_name' => $packagebundle->discount_id == null ? '-' : $packagebundle->discount_name ? $packagebundle->discount_name : $packagebundle->discount->name,
                'discount_type' => $packagebundle->discount_type == null ? '-' : $packagebundle->discount_type,
                'discount_price' => $packagebundle->discount_price == null ? '0.00' : $packagebundle->discount_price,
                'amount' => $packagebundle->tax_exclusive_net_amount,
                'tax_percentage' => $packagebundle->tax_percenatage,
                'tax_amount' => $packagebundle->tax_including_price,
                'detail' => array(),
            );
            $services = array();
            $packageservices = PackageService::where([['package_id', '=', $id], ['package_bundle_id', '=', $packagebundle->id]])->get();
            foreach ($packageservices as $serviceKey => $packageservice) {
                if($packageservice->is_consumed){
                    $consumed++;
                }
                $services[$serviceKey] = array(
                    'id' => $packageservice->id,
                    'name' => $packageservice->service->name,
                    'image_src' => $packageservice->service->image_src ? '/service_images/' . $packageservice->service->image_src : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg',
                    'amount' => $packageservice->tax_exclusive_price,
                    'tax_percentage' => $packageservice->tax_percenatage,
                    'tax_amount' => $packageservice->tax_including_price,
                    'is_consumed' => $packageservice->is_consumed == 0 ? 'No' : 'Yes'
                );
            }
            $bundles[$bundlekey]['detail'] = $services;
        }
        if ($consumed == $total_count) {
            $status = 'Consumed';
        } else if ($consumed < $total_count && $consumed > 0) {
            $status = 'Partially Consumed';
        } else {
            $status = 'Ready To Consume';
        }

        return array(
            'bundles' => $bundles,
            'status' => $status
        );
    }

    /*
     * That function give information of finance against plan
     */
    static function getplanfinancedetail($id)
    {
        $finance = array();
        $packageadvances = PackageAdvances::where([
            ['package_id', '=', $id],
            ['is_cancel', '=', '0'],
            ['is_refund', '=', '0']
        ])->get();
        if ($packageadvances) {
            foreach ($packageadvances as $advancekey => $packageadvance) {
                $finance[$advancekey] = array(
                    'id' => $packageadvance->id,
                    'payment_mode' => $packageadvance->paymentmode ? $packageadvance->paymentmode->name: 'Wallet',
                    'cash_flow' => $packageadvance->cash_flow,
                    'cash_amount' => $packageadvance->cash_amount,
                    'created_at' => Carbon::parse($packageadvance->created_at)->format('F j,Y h:i A')
                );
            }
        }
        return $finance;
    }

    /*
     * Create the plan when package is sell
     */
    static function saveplan($package_selling, $location, $request, $transaction)
    {

        $data = array();
        $data['random_id'] = md5(time() . rand(0001, 9999) . rand(78599, 99999));
        $data['sessioncount'] = 1;
        $data['total_price'] = $package_selling->offered_price;
        $data['is_exclusive'] = $package_selling->is_exclusive;
        $data['account_id'] = Auth::User()->account_id;
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

        self::packageAdvance($plan, $location, $request, $transaction);

        return true;
    }

    /*
     * Save the amount in package advance
     */
    static public function packageAdvance($plan, $location, $request, $transaction)
    {
        $data = array();
        $data['cash_flow'] = 'in';
        $data['cash_amount'] = $request->wallet == 'true' ? $request->amount : $transaction->amount;
        $data['account_id'] = Auth::User()->account_id;
        $data['patient_id'] = $plan->patient_id;
        $data['payment_mode_id'] = $request->wallet == 'true'? null : $transaction->payment_mode_id;
        $data['created_by'] = Auth::User()->id;
        $data['updated_by'] = Auth::User()->id;
        $data['package_id'] = $plan->id;
        $data['location_id'] = $location->id;
        if($request->wallet == 'true'){
            $data['wallet_id'] = $request->user()->wallet->id;
        } else {
            $data['transaction_id'] = $transaction->id;
        }

        $packageAdavances = PackageAdvances::create($data);

        if ($packageAdavances && isset($packageAdavances)) {
            if($request->wallet == 'true'){
                $record = array(
                    'cash_flow' => 'out',
                    'cash_amount' => $request->amount,
                    'wallet_id' => $request->user()->wallet->id,
                    'patient_id' => $request->user()->id,
                    'payment_mode_id' => 5,
                    'account_id' => 1
                );
                WalletMeta::create($record);
            }
        }
        /*Now sent message to user about cash received*/
        Invoice_Plan_Refund_Sms_Functions::PlanCashReceived_SMS($plan->id, $packageAdavances);

        return true;
    }
}