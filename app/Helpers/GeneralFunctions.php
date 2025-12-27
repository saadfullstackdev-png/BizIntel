<?php

/**
 * Created by PhpStorm.
 * User: REDSignal
 * Date: 3/22/2018
 * Time: 3:49 PM
 */

namespace App\Helpers;

use App\User;
use App\Models\Notification;
use App\Models\NotificationTemplates;
use App\Models\Appointments;
use Auth;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class GeneralFunctions
{
    static public function cleanNumber($phoneNumber)
    {
        $phoneNumber = str_replace(' ', '', $phoneNumber); // Replaces all spaces with hyphens.
        $phoneNumber = str_replace('-', '', $phoneNumber); // Replaces all spaces with hyphens.

        return self::cleanCountryCodes(preg_replace('/[^0-9\-]/', '', $phoneNumber)); // Removes special chars.
    }

    static private function cleanCountryCodes($phoneNumber)
    {
        // Remove Zero Leading
        if ($phoneNumber[0] == '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }
        // Remove Coutnry
        if ($phoneNumber[0] == '9' && $phoneNumber[1] == '2') {
            $phoneNumber = substr($phoneNumber, 2);
        }
        // Remove Zero Leading
        if ($phoneNumber[0] == '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }

    static function prepareNumber($phoneNumber)
    {
        // Adjust Country Code for Pakistan
        if ($phoneNumber[0] == '3' && (strlen($phoneNumber) >= 9 && strlen($phoneNumber) <= 11)) {
            return '92' . $phoneNumber;
        } else {
            return $phoneNumber;
        }
    }

    static function prepareNumber4Call($phoneNumber)
    {
        // Adjust Country Code for Pakistan
        if ($phoneNumber[0] == '3' && strlen($phoneNumber) == 10) {
            return '+92' . $phoneNumber;
        } else {
            return $phoneNumber;
        }
    }

    /**
     * @param $type in string form
     * @return number numeric constant value
     */
    static function AppointmentType($type)
    {
        return $type == config("constants.appointment_type_consultancy_string") ? config("constants.appointment_type_consultancy") : config("constants.appointment_type_service");
    }
}
