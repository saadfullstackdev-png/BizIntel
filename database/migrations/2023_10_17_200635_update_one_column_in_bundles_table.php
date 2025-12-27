<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOneColumnInBundlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropColumn('is_mobile');
        });

        Schema::table('bundles', function (Blueprint $table) {
            $table->unsignedInteger('is_mobile')->after('apply_discount')->nullable();
            $table->foreign('is_mobile')->references('id')->on('content_display_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropForeign(['is_mobile']);
            $table->dropColumn('is_mobile');
        });
    }
}
