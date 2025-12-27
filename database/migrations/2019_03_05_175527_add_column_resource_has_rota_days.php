<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnResourceHasRotaDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resource_has_rota_days', function (Blueprint $table) {
            $table->string('start_off')->after('end_time')->nullable();
            $table->string('end_off')->after('start_off')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resource_has_rota_days', function (Blueprint $table) {
            $table->dropColumn('start_off');
            $table->dropColumn('end_off');
        });
    }
}
