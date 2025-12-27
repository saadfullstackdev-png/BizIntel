<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBundleServicesPriceHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bundle_services_price_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bundle_id')->nullable();
            $table->double('bundle_price', 11, 2)->nullable();
            $table->double('bundle_services_price', 11, 2)->nullable();
            $table->unsignedInteger('service_id')->nullable();
            $table->double('service_price', 11, 2)->nullable();
            $table->unsignedTinyInteger('active')->default(1);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bundle_id')->references('id')->on('bundles');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
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
        Schema::dropIfExists('bundle_services_price_history');
    }
}
