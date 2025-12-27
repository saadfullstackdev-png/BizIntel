<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountApprovalsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_approvals', function (Blueprint $table) {

            $table->increments('id');

            $table->unsignedInteger('discount_id');
            $table->unsignedInteger('user_id');

            $table->timestamps();

            $table->foreign('discount_id')
                ->references('id')
                ->on('discounts');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discount_approvals');
    }
}
