<?php

namespace App\Helpers;

use App\Models\Settings;
use App\Models\SMSLogs;
use App\Models\SMSTemplates;
use App\Models\UserOperatorSettings;
use Carbon\Carbon;
use Auth;

class AuthSms
{
    /*
     * Function That sand sms for otp on registeration or Forget Password
     * @param: int $package_id
     * @param: int $packageAdavances
     * @return: string
     */
    static function OTP_SMS($phone, $otp)
    {
        $SMSTemplate = SMSTemplates::getBySlug('otp',1);

        if (!$SMSTemplate) {
            return array(
                'status' => false,
                'message' => 'OTP sms disabled',
            );
        }

        $preparedText = Self::prepareSMSContent($SMSTemplate->content, $otp);

        $setting = Settings::whereSlug('sys-current-sms-operator')->first();

        $UserOperatorSettings = UserOperatorSettings::getRecord(1, $setting->data);

        if ($setting->data == 1) {
            $SMSObj = array(
                'username' => $UserOperatorSettings->username, // Setting ID 1 for Username
                'password' => $UserOperatorSettings->password, // Setting ID 2 for Password
                'to' => GeneralFunctions::prepareNumber(GeneralFunctions::cleanNumber($phone)),
                'text' => $preparedText,
                'mask' => $UserOperatorSettings->mask, // Setting ID 3 for Mask
                'test_mode' => $UserOperatorSettings->test_mode, // Setting ID 3 Test Mode
            );
            $response = TelenorSMSAPI::SendSMS($SMSObj);
        } else {
            $SMSObj = array(
                'username' => $UserOperatorSettings->username, // Setting ID 1 for Username
                'password' => $UserOperatorSettings->password, // Setting ID 2 for Password
                'from' => $UserOperatorSettings->mask,
                'to' => GeneralFunctions::prepareNumber(GeneralFunctions::cleanNumber($phone)),
                'text' => $preparedText,
                'test_mode' => $UserOperatorSettings->test_mode, // Setting ID 3 Test Mode
            );
            $response = JazzSMSAPI::SendSMS($SMSObj);
        }
        return $response;
    }

    /**
     * Prepare SMS Contnet for OTP
     *
     * @param: int $package_id
     * @param: int $smsContent
     * @param: int $packageAdavances
     * @return: string
     */
    static public function prepareSMSContent($smsContent, $otp)
    {
        $smsContent = str_replace('##otp##', $otp, $smsContent);
        return $smsContent;
    }
}