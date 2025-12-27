<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountallocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discountallocations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('discount_id');
            $table->unsignedInteger('user_id');
            $table->string('year');
            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedInteger('account_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('discount_id')
                ->references('id')
                ->on('discounts');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discountallocations');
    }
}
