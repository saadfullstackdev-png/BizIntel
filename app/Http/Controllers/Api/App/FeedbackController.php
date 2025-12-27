<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\sendEmail;

class FeedbackController extends Controller
{
    public function feedbackSubmit(Request $request)
    {
        // First of all define validation rules
        $rules = [
            'user_id' => 'required',
            'subject' => 'required',
            'message' => 'required',
            'type' => 'required',
        ];
        // Define custom validation message for above validation
        $messages = [
            'user_id.required' => 'User Required',
            'subject.required' => 'Subject Required',
            'message.required' => 'Message Required',
            'type.required' => 'Type Required'
        ];
        // This can check validation and return new error message if found
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => 'Feedback submit failed',
                'data' => $validator->errors(),
                'status_code' => 422,
            ]);
        } else {
            $data = $request->all();
            $data['account_id'] = 1;
            if (Feedback::createRecord($data)) {
                $user = User::find($request->user_id);
                if ($user) {
                    $message = $request->type . " By <br /><br/><b>Name</b>:" . $user->name . "<br/><b>Email:</b>" . $user->email . "<br/><b>Phone:</b>" . $user->phone . "<br/><br/><b>Message:</b><br/><br/>" . $request->message;
                    $to = "hello@3dlifestyle.com.pk";
                    $subject = $request->subject;
                    $status = sendEmail::sendEmail($to, $subject, $message);
                    if (!$status) {
                        return response()->json(array(
                            'status' => false,
                            'message' => 'Something went wrong, please try again later.',
                            'status_code' => 500,
                        ));
                    }
                } else {
                    return [
                        'error' => 'User not found',
                    ];
                }
                return response()->json([
                    'status' => true,
                    'message' => "Feedback Submit successful",
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
}


