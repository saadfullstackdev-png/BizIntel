<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPackageBundlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_bundles', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_hold')->default(0)->after('active');
            $table->unsignedInteger('approved_by')->nullable()->after('is_hold');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users');
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
            $table->dropColumn('is_hold');
        });
    }
}
