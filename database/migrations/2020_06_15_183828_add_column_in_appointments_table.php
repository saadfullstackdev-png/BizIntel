<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->integer('scheduled_at_count')->default(0)->after('scheduled_time');
            $table->date('first_scheduled_date')->nullable()->after('scheduled_at_count');
            $table->time('first_scheduled_time')->nullable()->after('first_scheduled_date');
            $table->integer('first_scheduled_count')->default(0)->after('first_scheduled_time');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('scheduled_at_count');
            $table->dropColumn('first_scheduled_date');
            $table->dropColumn('first_scheduled_time');
            $table->dropColumn('first_scheduled_count');
        });
    }
}
