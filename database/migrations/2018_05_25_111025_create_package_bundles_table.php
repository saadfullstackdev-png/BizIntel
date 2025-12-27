<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagebundlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_bundles', function (Blueprint $table) {
            $table->increments('id');
            $table->String('random_id')->nullable();
            $table->tinyInteger('qty');
            $table->String('discount_type')->nullable();
            $table->double('discount_price', 11, 2)->nullable();
            $table->double('service_price', 11, 2);
            $table->double('net_amount', 11, 2);

            $table->unsignedInteger('discount_id')->nullable();
            $table->unsignedInteger('package_id')->nullable();
            $table->unsignedTinyInteger('active')->default(1);

            $table->timestamps();
            $table->softDeletes();

            /*Manage foreign Keys relationship*/
            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('package_id')->references('id')->on('packages');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_services');
    }
}
