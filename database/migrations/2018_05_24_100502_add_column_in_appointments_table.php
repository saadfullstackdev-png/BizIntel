<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {

            $table->unsignedInteger('resource_has_rota_day_id')->nullable()->after('service_id');
            $table->unsignedInteger('resource_has_rota_day_id_for_machine')->nullable()->after('resource_has_rota_day_id');

            $table->foreign('resource_has_rota_day_id', 'appointments_resource_has_rota_day')->references('id')->on('resource_has_rota_days');
            $table->foreign('resource_has_rota_day_id_for_machine', 'appointments_resource_has_rota_day_machine')->references('id')->on('resource_has_rota_days');
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
            $table->dropForeign('appointments_resource_has_rota_day');
            $table->dropColumn('resource_has_rota_day_id');

            $table->dropForeign('appointments_resource_has_rota_day_machine');
            $table->dropColumn('resource_has_rota_day_id_for_machine');
        });
    }
}
