<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Resources\TermsAndPoliciesResource;
use App\Models\TermsAndPolicies;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TermsConditionController extends Controller
{
    /*
     * Get the terms and condition
     */
    public function getTermscondition()
    {
        $user = Auth::user();
        try {
            $termscondition = TermsAndPolicies::where([
                ['active', 1],
                ['name', '=', 'term']
            ])->where('account_id', 1)->get();

            if(count($termscondition) > 0){
                return response()->json([
                    'status' => true,
                    'message' => 'Terms and Condition Found.',
                    'termscondtion' => TermsAndPoliciesResource::collection($termscondition),
                    'status_code' => 200,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Found.',
                    'termscondtion' => [],
                    'status_code' => 204,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => 402,
            ]);
        }
    }
    /*
     * Get the refund policy
     */
    public function getRefundPolicy()
    {
        $user = Auth::user();
        try {
            $getrefundpolicy = TermsAndPolicies::where([
                ['active', 1],
                ['name', '=', 'refund']
            ])->where('account_id', 1)->get();

            if(count($getrefundpolicy) > 0){
                return response()->json([
                    'status' => true,
                    'message' => 'Refund Policy Found.',
                    'termscondtion' => TermsAndPoliciesResource::collection($getrefundpolicy),
                    'status_code' => 200,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Found.',
                    'termscondtion' => [],
                    'status_code' => 204,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => 402,
            ]);
        }
    }
    /*
     * Get the privacy policy
     */
    public function getprivacypolicy()
    {
        try {
            $privacypolicy = TermsAndPolicies::where([
                ['active', 1],
                ['name', '=', 'policy']
            ])->where('account_id', 1)->get();

            if(count($privacypolicy) > 0){
                return response()->json([
                    'status' => true,
                    'message' => 'Privacy Policy Found.',
                    'privacypolicy' => TermsAndPoliciesResource::collection($privacypolicy),
                    'status_code' => 200,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Found.',
                    'privacypolicy' => [],
                    'status_code' => 204,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => 402,
            ]);
        }
    }
}
