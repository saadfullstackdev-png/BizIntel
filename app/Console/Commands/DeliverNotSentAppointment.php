<?php

namespace App\Console\Commands;

use App\Helpers\GeneralFunctions;
use App\Helpers\JazzSMSAPI;
use App\Helpers\TelenorSMSAPI;
use App\Models\Appointments;
use App\Models\NotificationLog;
use App\Models\Settings;
use App\Models\UserOperatorSettings;
use App\Models\SMSLogs;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Config;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class DeliverNotSentAppointment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointment:deliver-not-sent-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deliver not sent sms again';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start_time = Carbon::parse(Carbon::now())->subMinute(120)->setTimezone('Asia/Karachi')->format('Y-m-d H:i') . ':00';
        $end_time = Carbon::parse(Carbon::now())->addMinutes(5)->setTimezone('Asia/Karachi')->format('Y-m-d H:i') . ':59';

        $where = array();

        $where[] = array(
            'status',
            '=',
            0
        );
        $where[] = array(
            'created_at',
            '>=',
            $start_time
        );
        $where[] = array(
            'created_at',
            '<=',
            $end_time
        );

        $sms_logs = SMSLogs::where($where)->select('id', 'to', 'text', 'appointment_id')->get();

        $notification_logs = NotificationLog::where($where)->select('id', 'to', 'text', 'appointment_id', 'title')->get();

        if ($sms_logs) {
            foreach ($sms_logs as $sms_log) {
                $response_sms = $this->sendSMS($sms_log->id, $sms_log->to, $sms_log->text, $sms_log->appointment_id);
            }
        }

        if ($notification_logs) {
            foreach ($notification_logs as $notification_log) {
                $response_notification = $this->sendNotification($notification_log, $notification_log->to, $notification_log->text, $notification_log->appointment_id);
            }
        }
    }

    /**
     * Send SMS on booking of Appointment
     *
     * @param: int $smsId
     * @param: string $patient_phone
     * @param: string $preparedText
     * @param: int $appointmentId
     * @return: array|mixture
     */
    private function sendSMS($smsId, $patient_phone, $preparedText, $appointmentId)
    {
        $appointment = Appointments::find($appointmentId);

        $setting = Settings::whereSlug('sys-current-sms-operator')->first();

        $UserOperatorSettings = UserOperatorSettings::getRecord($appointment->account_id, $setting->data);

        if ($setting->data == 1) {
            $SMSObj = array(
                'username' => $UserOperatorSettings->username, // Setting ID 1 for Username
                'password' => $UserOperatorSettings->password, // Setting ID 2 for Password
                'to' => $patient_phone,
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
                'to' => $patient_phone,
                'text' => $preparedText,
                'test_mode' => $UserOperatorSettings->test_mode, // Setting ID 3 Test Mode
            );
            $response = JazzSMSAPI::SendSMS($SMSObj);
        }
        if ($response['status']) {
            SMSLogs::find($smsId)->update(['status' => 1]);
        }
        return $response;
    }

    /**
     * Send notification on booking of Appointment
     *
     * @param: int $smsId
     * @param: string $patient_phone
     * @param: string $preparedText
     * @param: int $appointmentId
     * @return: array|mixture
     */
    private function sendNotification($notification_log, $patient_phone, $preparedText, $appointmentId)
    {
        $appointment = Appointments::find($appointmentId);

        $user_info = User::find($appointment->patient_id);

        if ($appointment->source == 'MOBILE') {

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);

            $notificationBuilder = new PayloadNotificationBuilder($notification_log->title);
            $notificationBuilder->setBody($preparedText)->setSound('default');

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData(['title' => $notification_log->title, 'body' => $preparedText, 'largeIcon' => $notification_log->icon, 'content_available' => true, 'priority' => 'HIGH', 'type' => 'type', 'value' => 'value']);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $downstreamResponse = FCM::sendTo($user_info->app_token, $option, $notification, $data);

            if ($downstreamResponse->numberSuccess() > 0) {
                NotificationLog::find($notification_log->id)->update(['status' => 1]);
            }

            return $downstreamResponse;
        }
    }
}
