<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOneColumnPackageBundle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_bundles', function (Blueprint $table) {
            $table->unsignedInteger('is_allocate')->default(0)->after('random_id');
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
            $table->dropColumn('is_allocate');
        });
    }
}
