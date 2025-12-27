<?php

namespace App\Console\Commands;

use App\Helpers\GeneralFunctions;
use App\Helpers\TelenorSMSAPI;
use App\Jobs\SecondNotificationJob;
use App\Jobs\SecondSmsJob;
use App\Models\Accounts;
use App\Models\Appointments;
use App\Models\NotificationLog;
use App\Models\UserOperatorSettings;
use App\Models\SMSLogs;
use App\Models\SMSTemplates;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SecondMessageOfAppointment extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointment:2nd-message-on-appointment-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 2nd message one day before appointment at 8PM';

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
        $day = Carbon::now()->setTimezone('Asia/Karachi')->format('Y-m-d');
        $tomorrow = Carbon::parse(Carbon::now())->addDay()->setTimezone('Asia/Karachi')->format('Y-m-d');;

        $where = array();

        $where[] = array(
            'scheduled_date',
            '=',
            $tomorrow
        );
        $appointments = Appointments::join('users', 'users.id', '=', 'appointments.patient_id')->where($where)
            ->where(['appointments.appointment_status_allow_message' => 1])
            ->whereNull('coming_from')
            ->select('appointments.id as appointment_id', 'appointments.account_id', 'users.phone')
            ->get();

        $log_type_sms = '2nd_sms';
        $log_type_notification = '2nd_notification';

        if ($appointments) {

            foreach ($appointments as $appointment) {

                $account = Accounts::first();

                $smsLog = SMSLogs::where(array(
                    'to' => GeneralFunctions::prepareNumber(GeneralFunctions::cleanNumber($appointment->phone)),
                    'log_type' => $log_type_sms,
                ))
                    ->where('appointment_id', '=', $appointment->appointment_id)
                    ->whereDate('created_at', '=', $day)
                    ->select('id')->first();

                if (!$smsLog) {
                    /**
                     * Dispatch Second sms job
                     */
                    $job_sms = (new SecondSmsJob([
                        'account_id' => $account->id,
                        'appointment_id' => $appointment->appointment_id,
                        'phone' => $appointment->phone,
                        'log_type' => $log_type_sms
                    ]))->delay(Carbon::now()->addSeconds(2));

                    dispatch($job_sms);
                }

                $notificationLog = NotificationLog::where(array(
                    'to' => GeneralFunctions::prepareNumber(GeneralFunctions::cleanNumber($appointment->phone)),
                    'log_type' => $log_type_notification,
                ))
                    ->where('appointment_id', '=', $appointment->appointment_id)
                    ->whereDate('created_at', '=', $day)
                    ->select('id')->first();

                if (!$notificationLog) {
                    $job_notification = (new SecondNotificationJob([
                        'account_id' => $account->id,
                        'appointment_id' => $appointment->appointment_id,
                        'phone' => $appointment->phone,
                        'log_type' => $log_type_notification
                    ]))->delay(Carbon::now()->addSeconds(2));

                    dispatch($job_notification);
                }
            }
            Log::info("Second sms sent finally ");
        }
    }
}
