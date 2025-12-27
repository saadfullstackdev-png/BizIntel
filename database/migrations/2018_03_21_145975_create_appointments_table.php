<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('random_id', 50)->nullable();

            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->text('reason')->nullable();
            $table->tinyInteger('send_message')->default(0);

            $table->unsignedInteger('lead_id');
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('doctor_id');
            $table->unsignedInteger('region_id');
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('base_appointment_status_id')->nullable();
            $table->unsignedInteger('appointment_status_id')->nullable();
            $table->unsignedInteger('appointment_status_allow_message')->default(0);
            $table->unsignedInteger('cancellation_reason_id')->nullable();
            $table->unsignedInteger('service_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('converted_by')->nullable();

            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedTinyInteger('msg_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Manage Foreing Key Relationshops
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads');
            $table->foreign('patient_id')
                ->references('id')
                ->on('users');
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users');
            $table->foreign('region_id')
                ->references('id')
                ->on('regions');
            $table->foreign('city_id')
                ->references('id')
                ->on('cities');
            $table->foreign('location_id')
                ->references('id')
                ->on('locations');
            $table->foreign('service_id')
                ->references('id')
                ->on('services');
            $table->foreign('appointment_status_id')
                ->references('id')
                ->on('appointment_statuses');
            $table->foreign('cancellation_reason_id')
                ->references('id')
                ->on('cancellation_reasons');
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
        Schema::dropIfExists('appointments');
    }
}