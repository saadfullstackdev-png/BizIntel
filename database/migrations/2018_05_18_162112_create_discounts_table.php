<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 40)->default('default');
            $table->String('name')->nullable();
            $table->enum('type', array('Fixed', 'Percentage'));
            $table->double('amount', 11, 2)->nullabale();
            $table->String('description')->nullable();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->unsignedTinyInteger('active')->default(1);

            $table->unsignedInteger('account_id')->nullable();
            // Foreign Key Relationships
            $table->foreign('account_id')->references('id')->on('accounts');

            $table->timestamps();
            $table->softDeletes();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discounts');
    }
}
