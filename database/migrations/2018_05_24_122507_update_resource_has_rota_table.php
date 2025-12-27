<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateResourceHasRotaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resource_has_rota', function (Blueprint $table) {

            $table->unsignedInteger('resource_id')->nullable()->after('active');
            $table->foreign('resource_id', 'resource_has_rota_resource')->references('id')->on('resources');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resource_has_rota', function (Blueprint $table) {
            $table->dropForeign('resource_has_rota_resource');
            $table->dropColumn('resource_id');
        });
    }
}
