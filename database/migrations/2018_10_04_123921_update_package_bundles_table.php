<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePackageBundlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_bundles', function (Blueprint $table) {
            $table->unsignedInteger('bundle_id')->nullable()->after('discount_id');
            $table->foreign('bundle_id', 'package_bundles_bundle_id')->references('id')->on('bundles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_bundles', function (Blueprint $table) {
            $table->dropForeign('package_bundles_bundle_id');
            $table->dropColumn('bundle_id');
        });
    }
}
