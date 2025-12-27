<?php

namespace App\Jobs;

use App\Helpers\GeneralFunctions;
use App\Helpers\JazzSMSAPI;
use App\Helpers\TelenorSMSAPI;
use App\Models\Appointments;
use App\Models\NotificationLog;
use App\Models\NotificationTemplates;
use App\Models\Settings;
use App\Models\SMSLogs;
use App\Models\SMSTemplates;
use App\Models\UserOperatorSettings;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Config;

class SecondNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->queue = 'medium';
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get Appointment
            $appointment = Appointments::find($this->payload['appointment_id']);

            if ($appointment->source == 'MOBILE') {
                if ($appointment->appointment_type_id == Config::get('constants.appointment_type_consultancy')) {
                    // SEND SMS for Appointment Booked
                    if ($appointment->consultancy_type == 'virtual') {
                        $NotificationTemplate = NotificationTemplates::getBySlug('virtual-second-sms', $this->payload['account_id']); // 'second-sms' for virtual consultancy SMS
                    } else {
                        $NotificationTemplate = NotificationTemplates::getBySlug('second-sms', $this->payload['account_id']); // 'second-sms' for Appointment SMS
                    }
                } else {
                    // SEND SMS for Appointment Booked
                    $NotificationTemplate = NotificationTemplates::getBySlug('treatment-second-sms', $this->payload['account_id']); // 'second-sms' for Appointment SMS
                }

                $user_info = User::find($appointment->patient_id);

                if (!$NotificationTemplate || !$user_info->app_token) {
                    // SMS Promotion is disabled
                    return array(
                        'status' => true,
                        'sms_data' => 'Second Notification is disabled or App token not exists',
                        'error_msg' => '',
                    );
                }

                $preparedText = Appointments::prepareSMSContent($this->payload['appointment_id'], $NotificationTemplate->content);

                $largeIcon = '';

                if (!empty($NotificationTemplate->image_url)) {
                    $largeIcon = url('/') . '/notification_templates_images/' . $NotificationTemplate->image_url;//that should be image
                }

                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60 * 20);

                $notificationBuilder = new PayloadNotificationBuilder($NotificationTemplate->name);
                $notificationBuilder->setBody($preparedText)->setSound('default');

                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['title' => $NotificationTemplate->name, 'body' => $preparedText, 'largeIcon' => $largeIcon, 'content_available' => true, 'priority' => 'HIGH', 'type' => 'type', 'value' => 'value']);

                $option = $optionBuilder->build();
                $notification = $notificationBuilder->build();
                $data = $dataBuilder->build();

                $downstreamResponse = FCM::sendTo($user_info->app_token, $option, $notification, $data);

                $notificationLog = array(
                    'log_type' => $this->payload['log_type'],
                    'to' => GeneralFunctions::prepareNumber(GeneralFunctions::cleanNumber($this->payload['phone'])),
                    'text' => $preparedText,
                    'title' => $NotificationTemplate->name,
                    'type' => 'type',
                    'value' => 'value',
                    'icon' => $largeIcon,
                    'status' => $downstreamResponse->numberSuccess() > 0 ? true : false,
                    'error_msg' => '',
                    'appointment_id' => $this->payload['appointment_id'],
                    'created_by' => 1,
                    'patient_id' => $appointment->patient_id,
                );

                NotificationLog::create($notificationLog);

                return true;
            }
        } catch (\Exception $exception) {
            $exception->getLine() . '---' . $exception->getMessage() . '----' . $exception->getFile();
        }
    }
}
