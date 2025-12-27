<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedTinyInteger('msg_count')->default(0);

            // Foreign Key Relationships
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('lead_source_id')->nullable();
            $table->unsignedInteger('lead_status_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('converted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Manage Foreing Key Relationshops Mapping
            $table->foreign('patient_id')
                ->references('id')
                ->on('users');
            $table->foreign('city_id')
                ->references('id')
                ->on('cities');
            $table->foreign('region_id')
                ->references('id')
                ->on('regions');
            $table->foreign('lead_source_id')
                ->references('id')
                ->on('lead_sources');
            $table->foreign('lead_status_id')
                ->references('id')
                ->on('lead_statuses');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
            $table->foreign('converted_by')
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
        Schema::dropIfExists('leads');
    }
}