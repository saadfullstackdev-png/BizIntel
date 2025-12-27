<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePackageAdvancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_advances', function (Blueprint $table) {

            $table->unsignedInteger('appointment_type_id')->nullable()->after('account_id');
            $table->unsignedInteger('appointment_id')->nullable()->after('appointment_type_id');

            $table->foreign('appointment_type_id', 'patient_balances_appointment_type')->references('id')->on('appointment_types');
            $table->foreign('appointment_id', 'patient_balances_appointment')->references('id')->on('appointments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_advances', function (Blueprint $table) {
            $table->dropForeign('patient_balances_appointment_type');
            $table->dropColumn('appointment_type_id');

            $table->dropForeign('patient_balances_appointment');
            $table->dropColumn('appointment_id');

        });
    }
}
