<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        '\App\Console\Commands\SecondMessageOfAppointment',
        '\App\Console\Commands\ThirdMessageBeforeAppointment',
        '\App\Console\Commands\DeliverNotSentAppointment',
        '\App\Console\Commands\DeliverOnAppointmentBook',
        /*
         * MySQL daily backup command
         */
        '\App\Console\Commands\MySQLDump',
        '\App\Console\Commands\MySQLDumpRemover',
        /**
         * Sync Appointments into Elastic Search
         */
        '\App\Console\Commands\SyncAppointments',
        '\App\Console\Commands\HandleHeavyLifting',
        '\App\Console\Commands\InactiveDiscounts',

        /*
         * Moving Backup
         *
         * */

        '\App\Console\Commands\MoveBackup',

        /*
         * Sync the lead center column
         */
        '\App\Console\Commands\LeadCenterSync',

        /*
         * Sync package, plan and wallet
         */
        '\App\Console\Commands\MeezanSync',
        '\App\Console\Commands\SubscriptionCardActivationDeActivation',
        //    Update status of Appointments
        // '\App\Console\Commands\UpdateAppointmentStatus'

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
         * 2nd message one day before appointment at 8PM
         */
        $schedule->command('appointment:2nd-message-on-appointment-day')
            ->dailyAt('19:50')->timezone('Asia/Karachi');

        /*
         * 	3rd message 2 hours before appointment
         */
        // $schedule->command('appointment:3rd-message-before-appointment')
        //     ->everyThirtyMinutes();

        /*
         * Deliver SMS which failed to sent
         */
        $schedule->command('appointment:deliver-not-sent-sms')
            ->everyFifteenMinutes();

            /*
         * Deliver SMS on time of booking
         */
            // $schedule->command('appointment:deliver-on-appointment-book')
            //     ->withoutOverlapping()
            //     ->everyMinute()
        ;

        /*
         * Handle heavy lifting of jobs
         */
        $schedule->command('cms:handle-heavy-lifting')
            ->withoutOverlapping()
            ->everyMinute()
        ;

        /*
         * Run daily backup command
         */
        // $schedule->command('db:backup')
        //     ->dailyAt('23:59')->timezone('Asia/Karachi');

        /*
         * Run old daily backup remover command
         */
        // $schedule->command('db:backup-old-remove')
        //     ->dailyAt('23:55')->timezone('Asia/Karachi');


        /*
         * Inactive all the discounts which has previous day equals to the end date of the discount
         */

        $schedule->command('discounts:inactive')
            ->dailyAt('01:00')->timezone('Asia/Karachi');
        /*
         * Take backup of DATABASE and APPLICATION both
         * */

        // $schedule->command('backup:run')
        //     ->dailyAt('01:30')->timezone('Asia/Karachi');

        /*
         * Moving backup from ROLES-PERMISSION MANAGER to BACKUPS
         */

        // $schedule->command('move:backup')
        //     ->dailyAt('02:30')->timezone('Asia/Karachi');

        /*
         * Sync center for lead
         */

        //        $schedule->command('db:center')
        //            ->everyFifteenMinutes()->timezone('Asia/Karachi');

        /*
         * Sync meezan for plan, package and wallet
         */
        $schedule->command('sync:meezan')
            ->everyFiveMinutes()->timezone('Asia/Karachi');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
