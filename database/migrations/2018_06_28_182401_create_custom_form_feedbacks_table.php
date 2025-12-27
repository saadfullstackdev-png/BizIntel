<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFormFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_form_feedbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->text("form_name")->nullable();
            $table->text("form_description")->nullable();
            $table->text("content")->nullable();
            $table->unsignedInteger('custom_form_id');
            $table->unsignedInteger('reference_id')->nullable();
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('custom_form_type')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('custom_form_id')->references('id')->on('custom_forms');
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
        Schema::dropIfExists('custom_form_feedbacks');
    }
}
