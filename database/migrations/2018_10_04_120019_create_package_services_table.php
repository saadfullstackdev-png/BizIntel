<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_services', function (Blueprint $table) {

            $table->increments('id');

            $table->String('random_id')->nullable();
            $table->unsignedInteger('package_id')->nullable();
            $table->unsignedInteger('package_bundle_id')->nullable();
            $table->unsignedInteger('service_id')->nullable();
            $table->unsignedTinyInteger('is_consumed')->default(0);
            $table->double('orignal_price', 11, 2)->default(0.00);
            $table->double('price', 11, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('package_id')->references('id')->on('packages');
            $table->foreign('package_bundle_id')->references('id')->on('package_bundles');
            $table->foreign('service_id')->references('id')->on('services');
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
