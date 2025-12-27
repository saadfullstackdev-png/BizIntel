<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPackageAdvances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_advances', function (Blueprint $table) {
            $table->unsignedInteger('location_id')->nullable()->after('appointment_id');
            $table->foreign('location_id', 'package_location_id')->references('id')->on('locations');
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
            $table->dropForeign('package_location_id');
            $table->dropColumn('location_id');
        });
    }
}
