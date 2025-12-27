<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('qty');
            $table->string('discount_type')->nullable();
            $table->double('discount_price', 11, 2)->nullable();
            $table->double('service_price', 11, 2);
            $table->double('net_amount', 11, 2);

            $table->unsignedInteger('discount_id')->nullable();
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('package_id')->nullable();
            $table->unsignedInteger('invoice_id');

            $table->unsignedTinyInteger('active')->default(1);

            $table->timestamps();
            $table->softDeletes();

            /*Manage foreign Keys relationship*/
            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('package_id')->references('id')->on('packages');
            $table->foreign('invoice_id')->references('id')->on('invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_details');
    }
}
