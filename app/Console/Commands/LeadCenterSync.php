<?php

namespace App\Console\Commands;

use App\Models\Appointments;
use App\Models\DBBackups;
use App\Models\Leads;
use Illuminate\Console\Command;
use Config;

class LeadCenterSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:center';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the lead center column';

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
        $leads = Leads::whereNull('location_id')->whereNull('is_iterate')->take('15000')->orderBy('created_at', 'desc')->get();
        foreach ($leads as $lead){
            $appointment = Appointments::whereNotNull('location_id')->where('lead_id', '=', $lead->id)->orderBy('created_at', 'asc')->first();
            if($appointment){
                Leads::where('id', '=', $lead->id)->update(['location_id' => $appointment->location_id, 'is_iterate' => 1]);
            } else {
                Leads::where('id', '=', $lead->id)->update(['is_iterate' => 1]);
            }
        }
    }
}
