<?php

use Illuminate\Database\Seeder;
use App\Models\DateType;

class DateTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DateType::insert([
            1 => array(
                'id' => 1,
                'date_type' => 'Open',
                'slug' => 'open',
                'account_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            2 => array(
                'id' => 2,
                'date_type' => 'This Month',
                'slug' => 'this_month',
                'account_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            3 => array(
                'id' => 3,
                'date_type' => 'This Year',
                'slug' => 'this_year',
                'account_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            )
        ]);
    }
}
