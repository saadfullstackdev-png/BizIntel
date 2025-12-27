<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFourColumnInvoicedetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->double('tax_exclusive_serviceprice', 11, 2)->after('service_price')->default(0.00);
            $table->double('tax_percenatage', 11, 2)->after('tax_exclusive_serviceprice')->default(0.00);
            $table->double('tax_price', 11, 2)->after('tax_percenatage')->default(0.00);
            $table->double('tax_including_price', 11, 2)->after('tax_price')->default(0.00);
            $table->unsignedTinyInteger('is_exclusive')->after('tax_including_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropColumn('tax_exclusive_serviceprice');
            $table->dropColumn('tax_percenatage');
            $table->dropColumn('tax_price');
            $table->dropColumn('tax_including_price');
            $table->dropColumn('is_exclusive');
        });
    }
}
