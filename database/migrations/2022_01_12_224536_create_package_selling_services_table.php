<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageSellingServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_selling_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('package_selling_id');
            $table->unsignedInteger('bundle_id');
            $table->unsignedInteger('service_id');

            $table->double('autual_price', 11, 2)->default(0.00);
            $table->double('offered_price', 11, 2)->default(0.00);

            $table->unsignedTinyInteger('is_exclusive');
            $table->unsignedTinyInteger('is_consumed')->default(0);

            $table->double('tax_exclusive_price', 11, 2);
            $table->double('tax_percentage', 11, 2)->default(0.00);
            $table->double('tax_price', 11, 2)->default(0.00);
            $table->double('tax_including_price', 11, 2)->default(0.00);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('package_selling_id')
                ->references('id')
                ->on('package_sellings');

            $table->foreign('bundle_id')
                ->references('id')
                ->on('bundles');

            $table->foreign('service_id')
                ->references('id')
                ->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_selling_services');
    }
}
