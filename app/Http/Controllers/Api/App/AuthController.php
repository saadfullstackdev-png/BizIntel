<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Resources\UserResource;
use App\Mail\ResetPassword;
use App\Mail\ResetPasswordCode;
use App\Models\Appointments;
use App\Models\Discounts;
use App\Models\Settings;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\GeneralFunctions;
use App\Helpers\AuthSms;
use Image;
use Config;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Get the userinfo against phone (that user is already exist or not)
     */
    public function getuserinfo(Request $request)
    {
        $phone = GeneralFunctions::cleanNumber($request->phone);

        $userInfo = User::where([
            ['user_type_id', '=', '3'],
            ['active', '=', '1'],
            ['account_id', '=', 1],
            ['phone', '=', $phone]
        ])->get();

        $status = false;
        $users = array();

        if (count($userInfo) > 0) {
            $count = 0;
            foreach ($userInfo as $user) {
                if ($user->otp && $user->is_mobile_active && $user->is_mobile) {
                    $status = true;
                } else {
                    if ($user->is_mobile) {
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
                            'appointmentcount' => $user->appointmentdata->count()
                        );
                        return response()->json([
                            'status' => true,
                            'message' => "Kindly select user",
                            'data' => $users,
                            'status_code' => 500,
                        ]);
                    } else {
                        if ($count <= $user->appointmentdata->count()) {
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
                                'appointmentcount' => $user->appointmentdata->count()
                            );
                            $count = $user->appointmentdata->count();
                        }
                    }
                }
            }
            if ($status) {
                return response()->json([
                    'status' => false,
                    'message' => "That number already register, kindly click login or forget Password",
                    'status_code' => 422,
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => "Kindly select user",
                    'data' => $users,
                    'status_code' => 500,
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "No Record found against this number",
                'status_code' => 423,
            ]);
        }
    }

    /**
     * Generate otp after validation
     */
    public function generateOpt(Request $request)
    {
        // First of all define validation rules
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'gender' => 'required',
            'phone' => 'required',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
        ];
        // Define custom validation message for above validation
        $messages = [
            'name.required' => 'Name field is required',
            'email.required' => 'Email field is required',
            'password.required' => 'Password field is required',
            'password.min' => 'password must be at least 8 characters',
            'password.regex' => 'Password must be a combination of numbers, upper, lower, and special characters',
            'gender.required' => 'Gender field is required',
            'phone.required' => 'Phone field is required'
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
            // Check here to find, that phone number already exists not
            $userInfo = User::where([
                ['user_type_id', '=', '3'],
                ['active', '=', '1'],
                ['account_id', '=', 1],
                ['phone', '=', GeneralFunctions::cleanNumber($request->phone)]
            ])->get();

            $status = false;

            foreach ($userInfo as $user) {
                if ($user->otp && $user->is_mobile_active && $user->is_mobile) {
                    $status = true;
                }
            }

            if ($status) {
                return response([
                    'status' => false,
                    'message' => 'That number already register, kindly click login or forget Password',
                    'status_code' => 422,
                ]);
            } else {
                $data = $request->all();
                if ($request->user_id) {
                    $data['user_id'] = $request->user_id;
                    $user = User::where([
                        'id' => $request->user_id,
                        'account_id' => 1
                    ])->first();
                    $data['phone'] = GeneralFunctions::cleanNumber($user->phone);
                } else {
                    $data['phone'] = GeneralFunctions::cleanNumber($request->phone);
                }
                $data['name'] = $request->name;
                $data['email'] = $request->email;
                $data['gender'] = $request->gender;
                $data['cnic'] = $request->cnic;
                $data['dob'] = $request->dob;
                $data['user_type_id'] = Config::get('constants.patient_id');
                $data['address'] = $request->address;
                $data['image_src'] = $request->image_src;
                $data['lead_source_id'] = $request->lead_source_id;
                $data['password'] = $request->password;
                $data['otp'] = rand(1000, 9999);
                Mail::send([], [], function ($message) use ($data) {
                    $message->to($data['email'])
                        ->subject('OTP')
                        ->setBody('<h1>Your OTP is: ' . $data['otp'] . '</h1><p>Kindly verify your account with the OTP received.</p>', 'text/html');
                });
                AuthSms::OTP_SMS($data['phone'], $data['otp']);
                return response()->json([
                    'status' => true,
                    'message' => "Kindly verify your mobile number with the OTP received!",
                    'user' => $data,
                    'status_code' => 200,
                ]);
            }
        }
    }

    /**
     * Register or update user after otp
     */
    public function register(Request $request)
    {
        // First of all define validation rules
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'gender' => 'required',
            'phone' => 'required',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
        ];
        // Define custom validation message for above validation
        $messages = [
            'name.required' => 'Name field is required',
            'email.required' => 'Email field is required',
            'email.unique' => 'Email must be unique',
            'password.required' => 'Password field is required',
            'password.min' => 'password must be at least 8 characters',
            'password.regex' => 'Password must be a combination of numbers, upper, lower, and special characters',
            'gender.required' => 'Gender field is required',
            'phone.required' => 'Phone field is required',
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
            // Code for saving base64 image
            $image_src = null;
            if ($request->image_src) {
                $file_data = $request->image_src;
                $image_src = 'image_' . time() . '.png';
                @list($type, $file_data) = explode(';', $file_data);
                @list(, $file_data) = explode(',', $file_data);
                if ($file_data != "") {
                    file_put_contents(public_path() . '/patient_image/' . $image_src, base64_decode($file_data));
                }
            }

            $data = $request->all();
            $data['image_src'] = $image_src;
            $data['password'] = Hash::make($request->password);
            $data['account_id'] = 1;
            $data['user_type_id'] = Config::get('constants.patient_id');
            $data['otp'] = $request->otp;
            $data['is_mobile_active'] = 1;
            $data['is_mobile'] = 1;

            if ($request->user_id) {
                $user = User::where([
                    'id' => $request->user_id,
                    'account_id' => 1
                ])->first();
                if ($user) {
                    $data['phone'] = GeneralFunctions::cleanNumber($user->phone);
                    $user->update($data);
                    Appointments::where('patient_id', '=', $user->id)->update(['name' => $request->name]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "User Not exist",
                        'status_code' => 422,
                    ]);
                }
            } else {
                $data['phone'] = GeneralFunctions::cleanNumber($request->phone);
                $user = User::create($data);
            }

            $user['image_src'] = '/patient_image/' . $user->image_src;
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;

            // Now check any signup discount active
            $global_disocunt_setting = Settings::where('slug', '=', 'sys-signup-promotion')->first();

            $today = Carbon::now()->toDateString();

            $discount_information = Discounts::where([
                ['name', '=', $global_disocunt_setting->data],
                ['start', '<=', $today],
                ['end', '>=', $today],
                ['active', '=', '1']
            ])->first();

            $response = ['userProfile' => $user, 'token' => $token, 'discount' => $discount_information];

            return response()->json([
                'status' => true,
                'message' => "Registration successful",
                'data' => $response,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Update the userProfile
     */
    public function updateProfile(Request $request)
    {
        // First of all define validation rules
        $rules = [
            'name' => 'required',
            'gender' => 'required',
            'phone' => 'required'
        ];

        // Define custom validation message for above validation
        $messages = [
            'name.required' => 'Name field is required',
            'gender.required' => 'Gender field is required',
            'phone.required' => 'Phone field is required'
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
            // First of all get login user & then update user profile and return update user profile
            $user = Auth::user();

            // Code for saving base64 image
            if (isset($request->image_src)) {
                $file_data = $request->image_src;
                $image_src = 'image_' . time() . '.png';
                @list($type, $file_data) = explode(';', $file_data);
                @list(, $file_data) = explode(',', $file_data);
                if ($file_data != "") {
                    file_put_contents(public_path() . '/patient_image/' . $image_src, base64_decode($file_data));
                }
                $user->image_src = $image_src;
            }

            $user->name = $request->name;
            $user->gender = $request->gender;
            $user->cnic = $request->cnic;
            $user->dob = $request->dob;
            $user->address = $request->address;
            $user->phone = GeneralFunctions::cleanNumber($request->phone);
            $user->referred_by = $request->referred_by;

            $user->save();
            Appointments::where('patient_id', '=', $user->id)->update(['name' => $request->name]);
            $user->image_src = '/patient_image/' . $user->image_src;
            return response()->json([
                'status' => true,
                'message' => "Profile update successful",
                'data' => $user,
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Get the userProfile information
     */
    public function userProfile()
    {
        // This will get current user profile and pass current user to UserResource to get custom profile attributes
        $user = Auth::user();
        if ($user) {
            return response([
                'success' => true,
                'data' => new UserResource($user)
            ]);
        } else {
            return response([
                'success' => false,
                'data' => 'User not found'
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
                ['email', '=', $request->email],
                ['user_type_id', '=', '3']
            ])->get()->except($request->user_id);
        } else {
            $user = User::where([
                ['email', '=', $request->email],
                ['user_type_id', '=', '3']
            ])->get();
        }
        if (count($user) > 0) {
            return response()->json([
                'status' => false,
                'message' => "Email already exist!",
                'status_code' => 422,
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => "Email not exist!",
                'status_code' => 200,
            ]);
        }
    }

    /**
     * Delete the user
     */
    public function deleteUser(Request $request)
    {
        $request->user()->update(['otp' => Null, 'is_mobile_active' => 0]);
        $token = $request->user()->token();
        $token->revoke();
        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully',
            'status_code' => 200,
        ]);
    }
}
