<?php

namespace App\Http\Controllers\Api\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CardSubscription;
use App\Models\CardSubscriptionDetail;
use App\Models\WalletMeta;
use App\Models\SubscriptionCharge;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NodesTree;
class CardSubscriptionController extends Controller
{
    public function myCard(){
        try {
            $card = CardSubscription::where('patient_id',auth()->user()->id)->
            where('expiry_date','>=', date('Y-m-d H:i:s'))->where('is_active',1)->first();
            $subscription_charge = SubscriptionCharge::where('account_id',auth()->user()->account_id)->first();
            if($card){
           return response()->json([
                 'status' => true,
                 'card' => $card,
                 'subscription_discount' => $subscription_charge,    
                 'status_code' => 200,
             ]);  
            }else{
             return response()->json([
                 'status' => false,
                 'message' => "You have no Subscriptions",
                 'status_code' => 404,
             ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => 404,
            ]);
        }
    
    }
    public function subscription_discount(){
        try {
           $subscription_charge = SubscriptionCharge::join('category_subscription_charge', 'subscription_charges.id', '=', 'category_subscription_charge.subscription_charge_id')
            ->join('categories', 'categories.id', '=', 'category_subscription_charge.category_id') // Fixed table name
            ->where('subscription_charges.account_id', auth()->user()->account_id) // Ensure correct table reference
            ->get();
            if($subscription_charge){
           return response()->json([
                 'status' => true,
                 'subscription_discount' => $subscription_charge,
                 'status_code' => 200,
             ]);  
            }else{
             return response()->json([
                 'status' => false,
                 'message' => "You have no Subscriptions",
                 'status_code' => 404,
             ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => 404,
            ]);
        }
    
    }
    public function get_services(Request $request){
        try {
            $parentGroups = new NodesTree();
            $parentGroups->current_id = -1;
            $parentGroups->build(0, Auth::User()->account_id);
            $parentGroups->toList($parentGroups, -1);
            $Services = $parentGroups->nodeList;
             if($Services){
            return response()->json([
                  'status' => true,
                  'services_list' => $Services,    
                  'status_code' => 200,
              ]);  
             }else{
              return response()->json([
                  'status' => false,
                  'message' => "No Services Available",
                  'status_code' => 404,
              ]);
             }
         } catch (\Exception $e) {
             return response()->json([
                 'status' => false,
                 'message' => $e->getMessage(),
                 'status_code' => 404,
             ]);
         }
        

    }
    public function card_subscription(Request $request){
        $date = new \DateTime();
        $wallet_id = Wallet::where('patient_id',Auth::User()->id)->first()->id;
        $wallet_in = WalletMeta::where([
            ['cash_flow', '=', 'in'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $wallet_id]
        ])->sum('cash_amount');

        $wallet_out = WalletMeta::where([
            ['cash_flow', '=', 'out'],
            ['cash_amount', '>', 0],
            ['wallet_id', '=', $wallet_id]
        ])->sum('cash_amount');

        $walletbalance = $wallet_in - $wallet_out;
        if(SubscriptionCharge::first()->amount <= $request->amount && $walletbalance >= $request->amount){
            $subscription= CardSubscription::where('patient_id',Auth::User()->id)->first();
            if(!$subscription){
                CardSubscription::create([
                    'card_number' => round(microtime(true) * 1000).Auth::User()->id,
                    'patient_id' => Auth::User()->id,
                    'subscription_date' => date('Y-m-d H:i:s'),
                    'expiry_date' => $date->modify('+1 year')->format('Y-m-d H:i:s'),
                ]);
            }elseif($subscription){
                $subscription->update([
                    'subscription_date' => date('Y-m-d H:i:s'),
                    'expiry_date' => $date->modify('+1 year')->format('Y-m-d H:i:s'),
                    'is_active' => 1
                ]);
                CardSubscriptionDetail::create([
                    'subscription_card_id' => $subscription->id,
                    'amount' =>  $request->amount,
                    'account_id' => Auth::User()->id,
                ]);
            }
            if (CardSubscription::count() <=100) {
                WalletMeta::create([
                    'cash_flow' => 'in',
                    'cash_amount' => 5000,
                    'patient_id' => Auth::User()->id,
                    'wallet_id' => $wallet_id,
                ]);
            }
            WalletMeta::create([
                'cash_flow' => 'out',
                'cash_amount' => $request->amount,
                'patient_id' => Auth::User()->id,
                'wallet_id' => $wallet_id,
            ]);
            return response()->json([
                'success'     => true,
                'message'     => 'Card Subscribed successfully',
                'status_code' => 200,
            ], 200);
        }
    }

}
