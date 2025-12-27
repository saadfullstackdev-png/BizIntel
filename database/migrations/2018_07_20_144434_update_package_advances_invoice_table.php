<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePackageAdvancesInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_advances', function (Blueprint $table) {
            $table->unsignedInteger('invoice_id')->nullable()->after('package_id');
            $table->foreign('invoice_id','package_advances_invoice_id')->references('id')->on('invoices');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_advances', function (Blueprint $table) {
            $table->dropForeign('package_advances_invoice_id');
            $table->dropColumn('invoice_id');
        });

    }
}
