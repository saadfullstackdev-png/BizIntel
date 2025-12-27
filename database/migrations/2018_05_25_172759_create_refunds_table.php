<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('refund_amount');
            $table->unsignedInteger('invoice_id');
            $table->unsignedInteger('package_advance_id');
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('account_id');
            $table->timestamps();


            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->foreign('package_advance_id')->references('id')->on('package_advances');
            $table->foreign('patient_id')->references('id')->on('users');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('account_id')->references('id')->on('accounts');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refunds');
    }
}
