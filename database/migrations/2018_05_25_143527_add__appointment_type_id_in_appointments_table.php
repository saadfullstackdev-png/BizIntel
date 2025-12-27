<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppointmentTypeIdInAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {

            $table->unsignedInteger('appointment_type_id')->nullable()->after('service_id');
            $table->foreign('appointment_type_id', 'appointments_appointment_type')->references('id')->on('appointment_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign('appointments_appointment_type');
            $table->dropColumn('appointment_type_id');
        });
    }
}
