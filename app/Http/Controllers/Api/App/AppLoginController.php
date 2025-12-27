<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\AuthSms;
use App\Helpers\GeneralFunctions;
use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\Models\UserLoginLogs;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PharIo\Version\Exception;
use App\Http\Resources\UserResource;
use App\Models\Locations;
use App\Models\PaymentModes;


class AppLoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Login the user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $user = User::where([
            ['email', $request->email],
            ['is_mobile_active', '=', '1'],
            ['is_mobile', '=', '1'],
            ['un_subscribe', '0']
        ])->whereNotNull('otp')->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                return response()->json([
                    'status' => true,
                    'message' => "User Successfully Login!",
                    'token' => $token,
                    'userProfile' => new UserResource($user),
                    'status_code' => 200,
                ]);
            } else {
                $response = ["message" => "Password mismatch Or Account De-activated"];
                return response($response, 422);
            }
        } else {
            $response = ["message" => 'User does not exist'];
            return response($response, 422);
        }
    }

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }

    /**
     * Remember me function
     */
    public function rememberMe($token)
    {
        // this will check if remember me token is valid or invalid
        $user = User::where('api_remember_token', $token)->get()->first();
        if ($user) {
            return response([
                "success" => true,
                "message" => "Token is valid"
            ]);
        } else {
            return response([
                "success" => false,
                "message" => "Sorry, token not exists"
            ]);
        }
    }

    /**
     * Check user and save otp and send back user info and otp
     */
    public function getotpforgotpassword(Request $request)
    {
        $phone = GeneralFunctions::cleanNumber($request->phone);
        $userInfo = User::where([
            ['user_type_id', '=', '3'],
            ['active', '=', '1'],
            ['account_id', '=', 1],
            ['phone', '=', $phone],
            ['is_mobile_active', '=', '1'],
            ['is_mobile', '=', '1']
        ])->whereNotNull('otp')->first();
        if ($userInfo) {
            $otp = rand(1000, 9999);
            $userInfo->update(['otp' => $otp]);
            AuthSms::OTP_SMS($phone, $otp);
            Mail::send([], [], function ($message) use ($userInfo, $otp) {
                $message->to($userInfo->email)
                    ->subject('OTP')
                    ->setBody('<h1>Your OTP is: ' . $otp . '</h1><p>Kindly verify your account with the OTP received.</p>', 'text/html');
            });
            return response()->json([
                'status' => true,
                'message' => "Kindly verify your mobile number with the OTP received!",
                'user' => $userInfo,
                'status_code' => 200,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "User not find against this number!",
                'status_code' => 422,
            ]);
        }
    }

    /**
     * Reset the password
     */
    public function saveresetpassword(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user->otp == $request->otp) {
            $user->update(['password' => Hash::make($request->password)]);
            return response([
                'status' => true,
                'data' => $user,
                "message" => "Password change successfully"
            ]);
        } else {
            return response([
                "success" => false,
                "message" => "Sorry, otp is not match"
            ]);
        }
    }


    public function change_password(Request $request)
    {
        $user = User::find($request->id);
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (Hash::check($request->get('current_password'), $user->password)) {
            $user->password = $request->get('new_password');
            $user->save();

            return response()->json(array(
                'status' => 1,
                'message' => 'Password has been changed successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => 'Current password is incorrect.',
            ));
        }
    }
    protected function validator(Request $request)
    {
        $rules = [
            'id' => 'required',
            'current_password' => 'required',
            'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
            'confirm_password' => 'required|same:new_password',
        ];

        $messages = [
            'current_password.required' => 'Current Password field is required',
            'new_password.required' => 'New Password field is required',
            'new_password.min' => 'New password must be at least 8 characters',
            'new_password.regex' => 'New Password must be a combination of numbers, upper, lower, and special characters',
        ];
        return $validator = Validator::make($request->all(), $rules, $messages);
    }


    public function login_v2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'app_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $user = User::where([
            ['email', $request->email],
            ['is_mobile_active', '=', '1'],
            ['is_mobile', '=', '1'],
            ['un_subscribe', '0']
        ])->whereNotNull('otp')->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $user->update(['app_token' => $request->app_token]);
                return response()->json([
                    'status' => true,
                    'message' => "User Successfully Login!",
                    'locations' => Locations::get(),
                    'paymentmodes' => PaymentModes::get(),
                    'token' => $token,
                    'userProfile' => new UserResource($user),
                    'status_code' => 200,
                ]);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" => 'User does not exist'];
            return response($response, 422);
        }
    }
    public function doctor_login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'app_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $user = User::where([
            ['email', $request->email]
        ])->whereNotNull('otp')->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $user->update(['app_token' => $request->app_token]);
                return response()->json([
                    'status' => true,
                    'message' => "User Successfully Login!",
                    'token' => $token,
                    'userProfile' => new UserResource($user),
                    'status_code' => 200,
                ]);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" => 'User does not exist'];
            return response($response, 422);
        }
    }
    /**
     * Logout the user
     */
    public function logout_v2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $token = $request->user()->token();
        $token->revoke();
        $user = User::where([
            ['id', $request->id],
        ])->first();

        $user->update(['app_token' => '']);
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
}
