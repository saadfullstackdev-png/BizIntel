<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageSellingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_sellings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bundle_id');
            $table->unsignedInteger('patient_id');
            $table->string('name');
            $table->double('actual_price', 11, 2)->default(0.00);
            $table->double('offered_price', 11, 2)->default(0.00);
            $table->unsignedInteger('total_services')->default(0);
            $table->unsignedTinyInteger('apply_discount')->default(1);

            $table->unsignedTinyInteger('is_exclusive');
            $table->unsignedTinyInteger('is_refund')->default(0);

            $table->double('tax_exclusive_price', 11, 2);
            $table->double('tax_percentage', 11, 2)->default(0.00);
            $table->double('tax_price', 11, 2)->default(0.00);
            $table->double('tax_including_price', 11, 2)->default(0.00);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bundle_id')
                ->references('id')
                ->on('bundles');
            $table->foreign('patient_id')
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
        Schema::dropIfExists('package_sellings');
    }
}
