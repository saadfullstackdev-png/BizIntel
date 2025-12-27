<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInPackageBundlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_bundles', function (Blueprint $table) {
            $table->unsignedInteger('periodic_reference_id')->nullable()->after('package_id');
            $table->String('unique_id')->nullable()->after('random_id');
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
            $table->dropColumn('periodic_reference_id');
            $table->dropColumn('unique_id');
        });
    }
}
