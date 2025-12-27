<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->String('random_id')->nullable();
            $table->String('name')->nullable();
            $table->String('sessioncount');
            $table->double('total_price', 11, 2)->nullabale();
            $table->unsignedInteger('is_refund')->default(0);

            $table->unsignedInteger('account_id')->nullable();
            $table->unsignedInteger('patient_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();

            $table->unsignedTinyInteger('active')->default(1);

            $table->timestamps();
            $table->softDeletes();

            /*Manage foreign Keys relationship*/
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('patient_id')->references('id')->on('users');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
}
