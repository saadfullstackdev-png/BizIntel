<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('log_type', 100)->default('sms');
            $table->text('to');
            $table->text('text');

            $table->text('title')->nullable();
            $table->string('type')->nullable();
            $table->string('value')->nullable();
            $table->string('icon')->nullable();

            $table->unsignedTinyInteger('status')->nullable();
            $table->text('error_msg')->nullable();

            // Foreign Key Relationships
            $table->unsignedInteger('lead_id')->nullable();
            $table->unsignedInteger('appointment_id')->nullable();

            $table->unsignedInteger('invoice_id')->nullable();
            $table->unsignedInteger('package_id')->nullable();
            $table->unsignedInteger('promotion_id')->nullable();

            $table->string('is_refund')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('patient_id')->nullable();
            $table->unsignedTinyInteger('is_read')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lead_id')
                ->references('id')
                ->on('leads');
            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
            $table->foreign('package_id')
                ->references('id')
                ->on('packages');
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
        Schema::dropIfExists('notification_logs');
    }
}
