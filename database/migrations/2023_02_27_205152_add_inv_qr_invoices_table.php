<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvQrInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->tinyInteger('is_scanned')->default(0);
            $table->string('scanned_date')->nullable();
            $table->unsignedInteger('scanned_by')->nullable();
            $table->string('inv_qr')->nullable();

            $table->foreign('scanned_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('is_scanned');
            $table->dropColumn('scanned_date');
            $table->dropColumn('scanned_by');
            $table->dropColumn('inv_qr');
        });
    }
}
