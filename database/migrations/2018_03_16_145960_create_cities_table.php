<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 40)->default('custom');
            $table->string('name');
            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedInteger('sort_number')->nullable();
            $table->unsignedInteger('is_featured')->default(0);
            $table->unsignedInteger('region_id');
            $table->timestamps();
            $table->softDeletes();

            // Manage Foreing Key Relationshops
            $table->foreign('region_id')
                ->references('id')
                ->on('regions');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
    }
}