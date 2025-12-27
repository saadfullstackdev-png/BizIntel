<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvQrToInvoicelogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_scan_logs', function (Blueprint $table) {
            //
            $table->string('inv_qr')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_scan_logs', function (Blueprint $table) {
            //
            $table->dropColumn('inv_qr');
        });
    }
}
