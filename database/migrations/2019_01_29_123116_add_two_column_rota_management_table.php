<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoColumnRotaManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resource_has_rota', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_consultancy')->after('active')->default(1);
            $table->unsignedTinyInteger('is_treatment')->after('is_consultancy')->default(1);
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
            $table->dropColumn('is_consultancy');
            $table->dropColumn('is_treatment');
        });
    }
}
