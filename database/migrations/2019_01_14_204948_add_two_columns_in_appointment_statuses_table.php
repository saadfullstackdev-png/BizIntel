<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoColumnsInAppointmentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointment_statuses', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_cancelled')->after('is_arrived')->default(0);
            $table->unsignedTinyInteger('is_unscheduled')->after('is_cancelled')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointment_statuses', function (Blueprint $table) {
            $table->dropColumn('is_cancelled');
            $table->dropColumn('is_unscheduled');
        });
    }
}
