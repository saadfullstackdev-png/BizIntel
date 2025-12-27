<?php

namespace App\Jobs;

use App\Helpers\GeneralFunctions;
use App\Models\Appointments;
use App\Models\NotificationLog;
use App\Models\NotificationTemplates;
use App\Models\SMSTemplates;
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

class PromotionNotificationJob implements ShouldQueue
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

            $NotificationTemplate = NotificationTemplates::where([
                ['id', '=', $this->payload['promo_id']],
                ['active', '=', 1]
            ])->first();

            if (!$NotificationTemplate || !$NotificationTemplate->content) {
                // SMS Promotion is disabled
                return array(
                    'status' => true,
                    'sms_data' => 'Promotion Notification is disabled',
                    'error_msg' => '',
                );
            }

            $largeIcon = '';

            if (!empty($NotificationTemplate->image_url)) {
                $largeIcon = url('/') . '/notification_templates_images/' . $NotificationTemplate->image_url;//that should be image
            }

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);

            $notificationBuilder = new PayloadNotificationBuilder($NotificationTemplate->name);
            $notificationBuilder->setBody($NotificationTemplate->content)->setSound('default');

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData(['title' => $NotificationTemplate->name, 'body' => $NotificationTemplate->content, 'largeIcon' => $largeIcon, 'content_available' => true, 'priority' => 'HIGH', 'type' => 'type', 'value' => 'value']);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $downstreamResponse = FCM::sendTo($this->payload['token'], $option, $notification, $data);

            $notificationLog = array(
                'log_type' => $this->payload['log_type'],
                'to' => GeneralFunctions::prepareNumber(GeneralFunctions::cleanNumber($this->payload['phone'])),
                'text' => $NotificationTemplate->content,
                'title' => $NotificationTemplate->name,
                'type' => 'type',
                'value' => 'value',
                'icon' => $largeIcon,
                'status' => $downstreamResponse->numberSuccess() > 0 ? true : false,
                'error_msg' => '',
                'promotion_id' => $this->payload['promo_id'],
                'created_by' => 1,
                'patient_id' => $this->payload['patient_id'],
            );

            NotificationLog::create($notificationLog);

            return true;

        } catch (\Exception $exception) {
            $exception->getLine() . '---' . $exception->getMessage() . '----' . $exception->getFile();
        }
    }
}
