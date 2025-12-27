<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {

            $table->increments('id');
            $table->String('name');

            $table->unsignedInteger('resource_type_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('external_id')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            $table->unsignedTinyInteger('active')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('resource_type_id')->references('id')->on('resource_types');
            $table->foreign('location_id')->references('id')->on('locations');
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
        Schema::dropIfExists('resources');
    }
}
