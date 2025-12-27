<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoColumnInPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {

            $table->unsignedInteger('bundle_id')->nullable()->after('appointment_id');
            $table->unsignedInteger('package_selling_id')->nullable()->after('bundle_id');

            $table->foreign('bundle_id')
                ->references('id')
                ->on('bundles');

            $table->foreign('package_selling_id')
                ->references('id')
                ->on('package_sellings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('bundle_id');
            $table->dropColumn('package_selling_id');
        });
    }
}
