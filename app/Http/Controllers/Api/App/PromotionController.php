<?php

namespace App\Http\Controllers\Api\App;

use App\Models\Discounts;
use App\Models\Promotion;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    public function savepromotion(Request $request)
    {
        // First of all define validation rules
        $rules = [
            'user_id' => 'required',
            'discount_id' => 'required',
        ];
        // Define custom validation message for above validation
        $messages = [
            'user_id.required' => 'User Required',
            'discount_id.required' => 'Discount Required'
        ];
        // This can check validation and return new error message if found
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->messages()->all(),
                'status_code' => 422,
            ]);
        } else {
            $data = $request->all();
            $discount_info = Discounts::find($request->discount_id);
            $data['code'] = $random_id = md5(time() . rand(0001, 9999) . rand(78599, 99999));
            $data['account_id'] = Auth::User()->account_id;
            $data['discount_slug'] = $discount_info->slug;
            if (Promotion::createRecord($data)) {
                return response()->json([
                    'status' => true,
                    'message' => "Discount Taken successful",
                    'status_code' => 200,
                ]);
            } else {
                return response()->json(array(
                    'status' => false,
                    'message' => 'Something went wrong, please try again later.',
                    'status_code' => 500,
                ));
            }
        }
    }

    public function getpromotion(Request $request)
    {
        // Now check any signup discount active
        $global_disocunt_setting = Settings::where('slug', '=', 'sys-signup-promotion')->first();

        $today = Carbon::now()->toDateString();

        $discount_information = Discounts::where([
            ['name', '=', $global_disocunt_setting->data],
            ['start', '<=', $today],
            ['end','>=',$today],
            ['active', '=', '1']
        ])->first();

        $status = false;
        $discount_info = null;

        $promotions_info = Promotion::where('user_id', '=', $request->user_id)->get();
        $promotions = array();
        if (count($promotions_info)) {
            foreach ($promotions_info as $key => $promotion) {
                if($promotion->discount_id == $discount_information->id){
                    $status = true;
                }
                $promotions[$key] = array(
                    'id' => $promotion->id,
                    'discount_name' => $promotion->discount->name,
                    'user_name' => $promotion->user->name,
                    'code' => $promotion->code,
                    'use' => $promotion->use,
                    'taken' => $promotion->taken,
                );
            }
        }

        if(!$status){
            $discount_info = $discount_information;
        }
        return response()->json([
            'status' => true,
            'message' => 'Promotion data against user id',
            'selectedPromotion' => $promotions,
            'newPromotion' => $discount_info,
            'status_code' => 200,
        ]);
    }
}
