<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('payment_mode_id');
            $table->string('order_id')->nullable();
            $table->double('amount', 10,2)->default(0.00);
            $table->enum('paid_for', ['plan', 'package', 'wallet'])->nullable();
            $table->unsignedInteger('paid_for_id')->nullable();
            $table->enum('status', ['success', 'pending', 'cancelled']);
            $table->timestamps();

            // foreign key relations
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('payment_mode_id')->references('id')->on('payment_modes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
