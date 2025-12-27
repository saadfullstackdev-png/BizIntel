<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInvoiceStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_statuses', function (Blueprint $table) {
            $table->unsignedInteger('account_id')->nullable()->after('slug');
            $table->foreign('account_id','invoice_status_account_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_statuses', function (Blueprint $table) {
            $table->dropForeign('invoice_status_account_id');
            $table->dropColumn('account_id');
        });
    }
}
