<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CardSubscription;

class SubscriptionCardActivationDeActivation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'card:activation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Card Subscription status check';

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
        CardSubscription::where('expiry_date', '<',date('Y-m-d , H:i:s'))->where('is_active',1)->update([
            'is_active'=> 0
        ]);
    }
}
