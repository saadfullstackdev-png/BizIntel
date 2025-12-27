<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('to');
            $table->text('text');
            $table->text('mask')->nullable();
            $table->unsignedTinyInteger('status')->nullable();
            $table->text('sms_data')->nullable();
            $table->text('error_msg')->nullable();

            // Foreign Key Relationships
            $table->unsignedInteger('lead_id')->nullable();
            $table->unsignedInteger('appointment_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Manage Foreing Key Relationshops Mapping
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads');
            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments');
            $table->foreign('created_by')
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
        Schema::dropIfExists('sms_logs');
    }
}