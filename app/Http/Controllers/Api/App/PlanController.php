<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\Payments\Meezan;
use App\Models\Accounts;
use App\Models\Discounts;
use App\Models\Locations;
use App\Models\PackageAdvances;
use App\Models\PackageBundles;
use App\Models\Packages;
use App\Models\PackageService;
use App\Models\PaymentModes;
use App\Models\Services;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\WalletMeta;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Auth;
use App\Http\Controllers\Api\App\ApiHelpers\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    /**
     * Get the plan information against user id
     */
    public function getplans()
    {
        $records = array();

        $plans = Packages::where('patient_id', '=', Auth::User()->id)->whereNull('package_selling_id')->orderby('created_at', 'DESC')->get();

        if ($plans) {
            foreach ($plans as $key => $plan) {
                $session_count = count(PackageBundles::where('package_id', '=', $plan->id)->get());
                $total_price = PackageBundles::where('package_id', '=', $plan->id)->sum('tax_including_price');
                $cash_receive = PackageAdvances::where([
                    ['package_id', '=', $plan->id],
                    ['cash_flow', '=', 'in'],
                    ['is_cancel', '=', '0']
                ])->sum('cash_amount');
                if ($plan->is_refund == '0') {
                    $refund_status = 'No';
                } else {
                    $refund_status = 'Yes';
                }
                $records[$key] = array(
                    'id' => $plan->id,
                    'name' => $plan->user->name,
                    'location_id' => $plan->location->city->name." - ".$plan->location->name,
                    'session_count' => $session_count,
                    'total' => $total_price,
                    'cash_received' => $cash_receive,
                    'remaining' => $total_price - $cash_receive,
                    'refund' => $refund_status,
                    'created_at' => Carbon::parse($plan->created_at)->format('F j,Y h:i A'),
                    'status' => '',
                    'pdf' =>  '/getplans/pdf/'.$plan->id,
                    'bundle' => array(),
                    'finance' => Plan::getplanfinancedetail($plan->id)
                );
                $data = Plan::getplantreatmentdetail($plan->id);

                $records[$key]['bundle'] = $data['bundles'];
                $records[$key]['status'] = $data['status'];
            }
            return response([
                'success' => true,
                'message' => "Plan Data",
                'data' => $records
            ], 200);
        } else {
            return response([
                'success' => false,
                'message' => "Plan data not exist",
            ], 404);
        }
    }

    /**
     * Check validation for parameters passed in request
     *
     * @param Request $request
    */
    private function checkValidationGetPaymentForPlan(Request $request)
    {
        if($request->wallet){
            $rules = [
                'amount' => 'required'
            ];
            // Define custom validation message for above validation
            $messages = [
                'amount.required' => 'Order id is Required'
            ];
            // This can check validation and return new error message if found
        } else {
            $rules = [
                'transaction_id' => 'required',
                'plan_id' => 'required',
            ];
            // Define custom validation message for above validation
            $messages = [
                'transaction_id.required' => 'Order id is Required',
                'plan_id.required' => 'Plan id is Required',
            ];
            // This can check validation and return new error message if found
        }

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
    * Get the payment information against plan
    */
    public function getPaymentForPlan(Request $request)
    {
        try {
            $validator = $this->checkValidationGetPaymentForPlan($request);
            
            if($request->wallet == 'false'){
                $transaction = Transaction::where('user_id', $request->user()->id)->find($request->transaction_id);

                $orderStatus = Meezan::updateOrderStatus($transaction->order_id, $transaction);

                if ( !$orderStatus['status'] ){
                    return response([
                        'status' => false,
                        'message' => 'Transaction was not made successfully',
                        'data' => null,
                        'status_code' => 422,
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            if ($validator->fails()) {
                return response([
                    'success' => false,
                    'message' => $validator->messages()->all(),
                    'status_code' => 422,
                ]);
            }

            // Check if the entered amount is greater than total amount
            $package = Packages::whereActive(1)->wherePatient_id($request->user()->id)->find($request->plan_id);

            if ( !$package ){
                return response([
                    'status' => false,
                    'message' => 'Package not found !',
                    'status_code' => 404,
                ]);
            }

            $cash_receive = PackageAdvances::where([
                ['package_id', '=', $package->id],
                ['cash_flow', '=', 'in'],
                ['is_cancel', '=', '0'],
                ['active', '=', 1],
            ])->sum('cash_amount');

            if ( $cash_receive >= $package->total_price){
                return response([
                    'success' => false,
                    'message' => 'You have already paid amount for this plan',
                    'status_code' => 422,
                ]);
            }
            $balance_amount = $package->total_price - $cash_receive;

            $amount = $request->wallet == 'true' ? $request->amount : $transaction->amount;

            if ( $amount  > $balance_amount) {
                return response([
                    'success' => false,
                    'message' => 'Entered amount is more than balance',
                    'status_code' => 422,
                ]);
            }

            DB::beginTransaction();

            if($request->wallet == 'true'){
                $wallet_in = WalletMeta::where([
                    ['cash_flow','=','in'],
                    ['cash_amount','>',0],
                    ['wallet_id','=',$request->user()->wallet->id]
                ])->sum('cash_amount');

                $wallet_out = WalletMeta::where([
                    ['cash_flow','=','out'],
                    ['cash_amount','>',0],
                    ['wallet_id','=',$request->user()->wallet->id]
                ])->sum('cash_amount');

                $walletbalance = $wallet_in - $wallet_out;

                if($walletbalance < $request->amount){
                    return response([
                        'success' => false,
                        'message' => 'Your wallet balance is insufficient',
                        'status_code' => 422,
                    ]);
                }
            }
            /*Save data in package advances*/
            $data_packageAdvances['cash_flow'] = 'in';
            $data_packageAdvances['cash_amount'] = $amount;
            $data_packageAdvances['patient_id'] = $package->patient_id;
            $data_packageAdvances['payment_mode_id'] = $request->wallet == 'true'? null : $transaction->payment_mode_id;
            $data_packageAdvances['created_by'] = Auth::User()->id;
            $data_packageAdvances['updated_by'] = Auth::User()->id;
            $data_packageAdvances['package_id'] = $package->id;
            $data_packageAdvances['location_id'] = $package->location_id;
            if($request->wallet == 'true'){
                $data_packageAdvances['wallet_id'] = $request->user()->wallet->id;
            } else {
                $data_packageAdvances['transaction_id'] = $transaction->id;
            }

            $data_packageAdvances['account_id'] = 1;
            /*End*/

            $packageAdavances = PackageAdvances::createRecord($data_packageAdvances, $package);

            if ($packageAdavances && isset($packageAdavances)) {
                if($request->wallet == 'true'){
                    $record = array(
                        'cash_flow' => 'out',
                        'cash_amount' => $amount,
                        'wallet_id' => $request->user()->wallet->id,
                        'patient_id' => $request->user()->id,
                        'payment_mode_id' => 5,
                        'account_id' => 1
                    );
                    WalletMeta::create($record);
                }

                DB::commit();

                return response([
                    'success' => true,
                    'message' => 'Your data has been saved successfully !',
                    'status_code' => 200,
                ]);
            }

            return response([
                'success' => false,
                'message' => 'Some error occurred! Please try again',
                'status_code' => 200,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Some issue Occurred! Please try again !',
                'status_code' => 422,
            ]);
        }
    }

    /**
     * Print the package.
     */
    public function get_plan_pdf($id)
    {
        $package = Packages::find($id);

        $location_info = Locations::find($package->location_id);

        $account_info = Accounts::find($package->account_id);

        $packagebundles = PackageBundles::where('package_id', '=', $package->id)->get();

        $packageservices = PackageService::where('package_id', '=', $package->id)->get();

        $packageadvances = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['is_cancel', '=', '0'],
            ['is_refund', '=', '0']
        ])->get();

        $cash_amount_in = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'in']
        ])->sum('cash_amount');

        $cash_amount_out = PackageAdvances::where([
            ['package_id', '=', $package->id],
            ['cash_flow', '=', 'out']
        ])->sum('cash_amount');

        $cash_amount = $cash_amount_in - $cash_amount_out;
        /*We discuss it in future what happen next*/
        $grand_total = number_format($package->total_price - $cash_amount_in);

        $services = Services::getServices();
        $discount = Discounts::getDiscount(session('account_id'));

        $paymentmodes = PaymentModes::get()->pluck('name', 'id');
        $paymentmodes->prepend('Select Payment Mode', '');

        $company_phone_number = Settings::where('slug', '=', 'sys-headoffice')->first();

        $content = view('admin.packages.packagepdf', compact('package', 'packagebundles', 'packageservices', 'packageadvances', 'services', 'discount', 'paymentmodes', 'grand_total', 'location_info', 'account_info', 'company_phone_number'));
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($content);
        return $pdf->stream('Plans Invoice');

    }

}
