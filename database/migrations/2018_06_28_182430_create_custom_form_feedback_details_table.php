<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFormFeedbackDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_form_feedback_details', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('field_type');
            $table->text("field_label")->nullable();
            $table->text("field_value")->nullable();
            $table->text('content')->nullable();
            $table->unsignedInteger('section_id')->default(1)->comment("this will be used when one want to create form with multiple sections");
            $table->unsignedInteger('custom_form_id');
            $table->unsignedInteger('custom_form_field_id');
            $table->unsignedInteger('custom_form_feedback_id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('custom_form_id')->references('id')->on('custom_forms');
            $table->foreign('custom_form_field_id')->references('id')->on('custom_form_fields');
            $table->foreign('custom_form_feedback_id')->references('id')->on('custom_form_feedbacks');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_form_feedback_details');
    }
}
