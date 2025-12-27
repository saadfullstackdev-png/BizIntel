<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAppointmentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointment_statuses', function (Blueprint $table) {

            $table->unsignedInteger('appointment_type_id')->nullable()->after('sort_no');
            $table->foreign('appointment_type_id', 'appointment_statuses_appointment_type')->references('id')->on('appointment_statuses');
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
            $table->dropForeign('appointment_statuses_appointment_type');
            $table->dropColumn('appointment_type_id');
        });
    }
}
