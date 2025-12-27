<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoColumnSmsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->unsignedInteger('invoice_id')->nullable()->after('appointment_id');
            $table->unsignedInteger('package_id')->nullable()->after('invoice_id');
            $table->string('is_refund')->nullable()->after('package_id');

            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
            $table->foreign('package_id')
                ->references('id')
                ->on('packages');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
            $table->dropColumn('package_id');
            $table->dropColumn('is_refund');
        });
    }
}
