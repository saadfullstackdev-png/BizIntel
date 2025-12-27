<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('comment')->nullable();

            $table->unsignedInteger('appointment_id');
            $table->unsignedInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Manage Foreing Key Relationshops Mapping
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
        Schema::dropIfExists('appointment_comments');
    }
}
