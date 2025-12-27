<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SocialLoginController extends Controller
{
    // public function social_setup(Request $request)
    // {
    //     if ($this->isAccountExists($request)) {

    //         $user = User::where([
    //             ['social_account_id', $request->social_account_id],
    //             ['is_mobile_active', '=', '1'],
    //             ['is_mobile', '=', '1']
    //         ]);
    //         $user->update(['app_token' => $request->firebaseToken], ['is_mobile_active' => 1], ['is_mobile' => 1]);
    //         return $this->login($request);
    //     } else {
    //         $user = new User();
    //         $user->social_account_id = $request->social_account_id;
    //         $user->social_account_type = $request->social_account_type;
    //         $user->name = $request->name;
    //         $user->image_src = $request->social_account_profile_image_url;
    //         $user->email = $request->social_account_email;
    //         $user->app_token = $request->firebaseToken;
    //         $user->is_mobile_active = 1;
    //         $user->is_mobile = 1;
    //         $user->user_type_id = 3;// user type patient
    //         if ($this->checkemail($request)) {
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => "This Email Already Register Without Social Login. Please login or Reset Password",
    //                 'status_code' => 422,
    //             ]);
    //         }
    //         $user->password = Hash::make('password');
    //         $user->save();
    //         return $this->login($request);
    //     }
    // }
    public function social_setup(Request $request)
    {
        if ($this->checkemail($request)) {

            $user = User::where([
                ['email', $request->social_account_email],
                ['is_mobile_active', '=', '1'],
                ['is_mobile', '=', '1'],
                ['active', '=', '1'],
                ['user_type_id', '=', '3'],
                ['account_id', '=', 1]
            ])->first();

            if($user){
                $user->update(['app_token' => $request->firebaseToken]);
                return $this->login($request);
            }else{
                $user = User::where([
                ['email', $request->social_account_email],
                ['active', '=', '1'],
                ['user_type_id', '=', '3'],
                ['account_id', '=', 1]
                ])->first();
                if($user){
                    $users = array(
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'phone' => $user->phone,
                                'gender' => $user->gender,
                                'cnic' => $user->cnic,
                                'dob' => $user->dob,
                                'address' => $user->address,
                                'image_src' => $user->image_src ? '/patient_image/' . $user->image_src : '',
                                'active' => $user->active,
                                'lead_source_id' => $user->lead_source_id,
                                'is_mobile_active' => $user->is_mobile_active,
                                'is_mobile' => $user->is_mobile,
                                'appointmentcount' => $user->appointmentdata->count(),
                                'social_account_email' => $request->social_account_email,
                                'social_account_id' => $request->social_account_id,
                                'social_account_profile_image_url' => $request->social_account_profile_image_url,
                                'social_account_name' => $request->name
                            );
                    return response()->json([
                    'status' => false,
                    'message' => "User Already Exist But Not Mobile Active",
                    'status_code' => 500,
                    'data' => $users,
                    
                ]);

                }else{
                    return response()->json([
                    'status' => false,
                    'message' => "User is De-Activated By Administrator. Please Contact Us to Re-Activate your Account",
                    'status_code' => 500,
                    'data' => '',
                    
                ]);
                }
            }
            
        } else {
            $users = array(
                        'social_account_email' => $request->social_account_email,
                        'social_account_id' => $request->social_account_id,
                        'social_account_profile_image_url' => $request->social_account_profile_image_url,
                        'social_account_name' => $request->name
                    );
            return response()->json([
                    'status' => false,
                    'message' => "Email not Exist",
                    'status_code' => 200,
                    'data' => $users,
                    
                ]);
        }
    }

    // private function isAccountExists(Request $request)
    // {
    //     // verify it with email
    //     $social_account_id = $request->social_account_id;
    //     $user = User::where('social_account_id', $social_account_id)->get();
    //     if (count($user) > 0) {
    //         return true;
    //     }
    //     return false;
    // }

    private function login(Request $request)
    {
        $user = User::where('email', $request->social_account_email)->first();
        if ($user) {
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;

            return response()->json([
                'status' => true,
                'message' => "User Successfully Login!",
                'token' => $token,
                'userProfile' => new UserResource($user),
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Check that email is exit or not
     */
    public function checkemail(Request $request)
    {
        if ($request->user_id) {
            $user = User::where([
                ['email', '=', $request->social_account_email],
                ['user_type_id', '=', '3']
            ])->get()->except($request->user_id);
        } else {
            $user = User::where([
                ['email', '=', $request->social_account_email],
                ['user_type_id', '=', '3']
            ])->get();
        }
        if (count($user) > 0) {
            return true;
        }

        return false;
    }

}
