<?php

namespace App\Http\Controllers\Api\App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\sendEmail;


class GenericEmailServiceController extends Controller
{
    public function sendEmail(Request $request)
    {
        // Add this line
        $request->validate([
            'recipient_email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $to = $request->input('recipient_email');
        $subject = $request->input('subject');
        $message = $request->input('message');

        $status = sendEmail::sendEmail($to, $subject, $message);
        if ($status) {
            return response()->json(['message' => 'Email sent successfully']);
        } else {
            return response()->json(['message' => 'Email not sent']);
        }
    }
}