<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('cash_flow', array('in', 'out'))->default('in');
            $table->double('cash_amount', 11,2)->default(0.00);
            $table->unsignedInteger('is_refund')->default(0);
            $table->longText('refund_note')->nullable();
            $table->unsignedInteger('wallet_id');
            $table->unsignedInteger('patient_id')->nullable();
            $table->unsignedInteger('payment_mode_id')->nullable();
            $table->unsignedInteger('account_id')->nullable();
            $table->unsignedInteger('transaction_id')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets');
            $table->foreign('patient_id')->references('id')->on('users');
            $table->foreign('payment_mode_id')->references('id')->on('payment_modes');
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
        Schema::dropIfExists('wallet_metas');
    }
}
