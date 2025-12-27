<?php

namespace App\Http\Controllers\Api\App\ApiHelpers;

use App\Models\BundleHasServices;
use App\Models\PackageBundles;
use App\Models\PackageSellingService;
use App\Models\PackageService;
use App\Models\Services;
use Auth;
use Config;

class Package
{
    /*
     * That function give information of treatments against specific package
     */
    static function getpackagereatmentdetail($package)
    {
        $relationships = BundleHasServices::where(array(
            'bundle_id' => $package->id
        ))->select('service_id')->get();

        $bundle_services = collect(new Services());

        if ($relationships->count()) {
            $bundle_services = Services::whereIn('id', $relationships)->where(['account_id' => 1])->get()->getDictionary();
        }

        $treatments = array();
        if ($relationships) {
            foreach ($relationships as $treatkey => $relationship) {
                if (array_key_exists($relationship->service_id, $bundle_services)) {
                    $treatments[$treatkey] = array(
                        'name' => $bundle_services[$relationship->service_id]->name,
                        'price' => $bundle_services[$relationship->service_id]->price,
                        'image' => $bundle_services[$relationship->service_id]->image_src ? '/service_images/' . $bundle_services[$relationship->service_id]->image_src : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg'
                    );
                }
            }
        }
        return $treatments;
    }

    /*
     * Get the calculation of package regarding tax type
     */
    static function packagetaxcal($amount, $tax_percentage, $tax_treatment_type_id)
    {
        if ($tax_treatment_type_id == Config::get('constants.tax_both') || $tax_treatment_type_id == Config::get('constants.tax_is_exclusive')) {
            $price = $price_tax = $amount;
            $tax = ceil($price * ($tax_percentage / 100));
            $tax_amt = ceil($price + $tax);
            $is_exclusive = 1;
        } else {
            $tax_amt = $price_tax = $amount;
            $price = ceil((100 * $tax_amt) / ($tax_percentage + 100));
            $tax = ceil($tax_amt - $price);
            $is_exclusive = 0;
        }

        $data = array(
            'price' => $price,
            'tax' => $tax,
            'tax_amt' => $tax_amt,
            'is_exclusive' => $is_exclusive
        );
        return $data;
    }

    /*
     * Get the calculation of package selling services
     */
    static function getpackageSellingService($id)
    {
        $records = array();
        $packageSellingServices = PackageSellingService::where('package_selling_id', '=', $id)->get();

        $total_count = count($packageSellingServices);
        $consumed = 0;
        $status = '';

        foreach ($packageSellingServices as $key => $packageSellingService) {
            if ($packageSellingService->is_consumed) {
                $consumed++;
            }
            $records[$key] = array(
                'id' => $packageSellingService->id,
                'service' => $packageSellingService->service->name,
                'image_src' => $packageSellingService->service->image_src ? '/service_images/' . $packageSellingService->service->image_src : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg',
                'is_consumed' => $packageSellingService->is_consumed ? 'Yes' : 'No',
                'tax_including_price' => number_format($packageSellingService->tax_including_price, 2),
            );
        }
        if ($consumed == $total_count) {
            $status = 'Consumed';
        } else if ($consumed < $total_count && $consumed > 0) {
            $status = 'Partially Consumed';
        } else {
            $status = 'Ready To Consume';
        }
        return array(
            'records' => $records,
            'status' => $status
        );
    }
}