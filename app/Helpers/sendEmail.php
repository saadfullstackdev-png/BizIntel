<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class sendEmail
{
    public static function sendEmail($to, $subject, $message)
    {
        try {
            Mail::send([], [], function ($msg) use ($to, $subject, $message) {
                $msg->to($to)
                    ->from('3dlifestyles.pk@gmail.com', '3D LifeStyle Notification')
                    ->subject($subject)
                    ->setBody($message, 'text/html');
            });
            Log::info('Email sent successfully to: ' . $to);
            return true;
        } catch (\Exception $e) {
            Log::error('Email sending failed to: ' . $to . '. Error: ' . $e->getMessage());
            return false;
        }
    }
}