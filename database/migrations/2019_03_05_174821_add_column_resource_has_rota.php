<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnResourceHasRota extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resource_has_rota', function (Blueprint $table) {
            $table->string('monday_off')->after('monday')->nullable();
            $table->string('tuesday_off')->after('tuesday')->nullable();
            $table->string('wednesday_off')->after('wednesday')->nullable();
            $table->string('thursday_off')->after('thursday')->nullable();
            $table->string('friday_off')->after('friday')->nullable();
            $table->string('saturday_off')->after('saturday')->nullable();
            $table->string('sunday_off')->after('sunday')->nullable();
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
            $table->dropColumn('monday_off');
            $table->dropColumn('tuesday_off');
            $table->dropColumn('wednesday_off');
            $table->dropColumn('thursday_off');
            $table->dropColumn('friday_off');
            $table->dropColumn('saturday_off');
            $table->dropColumn('sunday_off');
        });
    }
}
