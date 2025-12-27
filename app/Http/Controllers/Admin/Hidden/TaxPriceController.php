<?php

namespace App\Http\Controllers\Admin\Hidden;

use App\Models\InvoiceDetails;
use App\Models\PackageBundles;
use App\Models\Packages;
use App\Models\PackageService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaxPriceController extends Controller
{
    public function invoicetaxprice()
    {

        $invoice_detail_information = InvoiceDetails::where('is_exclusive', '=', 0)->get();

        foreach ($invoice_detail_information as $invoice_detail_info) {

            $new_tax_price = $invoice_detail_info->tax_including_price - $invoice_detail_info->tax_exclusive_serviceprice;

            $invoice_detail_info->update(['tax_price' => $new_tax_price]);
        }
        return view('/home');
    }

    public function plantaxprice()
    {

        $package_information = Packages::where('is_exclusive', '=', '0')->get();

        foreach ($package_information as $package_info) {

            $package_bundle_information = PackageBundles::where('package_id', '=', $package_info->id)->get();

            foreach ($package_bundle_information as $package_bundle) {

                $new_tax_price_bundle = $package_bundle->tax_including_price - $package_bundle->tax_exclusive_net_amount;

                $package_bundle->update(['tax_price' => $new_tax_price_bundle]);

                $package_service_information = PackageService::where([
                    ['package_id', '=', $package_info->id],
                    ['package_bundle_id', '=', $package_bundle->id]
                ])->get();

                foreach ($package_service_information as $package_service) {

                    $new_tax_price_service = $package_service->tax_including_price - $package_service->tax_exclusive_price;

                    $package_service->update(['tax_price' => $new_tax_price_service]);
                }
            }
        }
        return view('/home');
    }
}
