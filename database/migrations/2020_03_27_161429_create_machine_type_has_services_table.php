<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMachineTypeHasServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_type_has_services', function (Blueprint $table) {

            $table->unsignedInteger('machine_type_id');
            $table->unsignedInteger('service_id');

            // Manage Foreing Key Relationshops
            $table->foreign('machine_type_id')
                ->references('id')
                ->on('machine_types')
                ->onDelete('cascade');
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');

            $table->primary(['machine_type_id', 'service_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('machine_type_has_services');
    }
}
