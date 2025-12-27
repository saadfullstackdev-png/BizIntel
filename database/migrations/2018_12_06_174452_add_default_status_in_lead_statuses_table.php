<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultStatusInLeadStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_default')->after('is_comment')->default(0);
            $table->unsignedTinyInteger('is_arrived')->after('is_default')->default(0);
            $table->unsignedTinyInteger('is_converted')->after('is_arrived')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->dropColumn('is_default');
            $table->dropColumn('is_arrived');
            $table->dropColumn('is_converted');
        });
    }
}
