<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBundleHasServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bundle_has_services', function (Blueprint $table) {
            $table->unsignedInteger('bundle_id');
            $table->unsignedInteger('service_id');
            $table->double('service_price', 11,2)->default(0.00);
            $table->double('calculated_price', 11, 2)->default(0.00);
            $table->unsignedTinyInteger('end_node')->default(1);

            // Manage Foreing Key Relationshops
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');
            $table->foreign('bundle_id')
                ->references('id')
                ->on('bundles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bundle_has_services');
    }
}
