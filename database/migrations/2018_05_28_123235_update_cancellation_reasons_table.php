<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCancellationReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cancellation_reasons', function (Blueprint $table) {
            $table->unsignedInteger('appointment_type_id')->nullable()->after('active');
            $table->foreign('appointment_type_id', 'cancellation_reasons_appointment_type')->references('id')->on('appointment_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cancellation_reasons', function (Blueprint $table) {

            $table->dropForeign('cancellation_reasons_appointment_type');
            $table->dropColumn('appointment_type_id');
        });
    }
}
