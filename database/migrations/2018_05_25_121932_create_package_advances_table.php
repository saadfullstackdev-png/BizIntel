<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageAdvancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_advances', function (Blueprint $table) {

            $table->increments('id');
            $table->enum('cash_flow', array('in', 'out'))->default('in');
            $table->double('cash_amount', 11,2)->default(0.00);
            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedInteger('is_refund')->default(0);
            $table->unsignedInteger('is_adjustment')->default(0);
            $table->longText('refund_note')->nullable();
            $table->unsignedInteger('is_cancel')->default(0);
            $table->unsignedInteger('patient_id')->nullable();
            $table->unsignedInteger('payment_mode_id')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('package_id')->nullable();


            /*$table->unsignedInteger('session_id')->nullable();
            $table->unsignedInteger('consultancey_id')->nullable();*/

            // Manage Foreign Key Relationships
            $table->foreign('patient_id')->references('id')->on('users');
            $table->foreign('payment_mode_id')->references('id')->on('payment_modes');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('package_id')->references('id')->on('packages');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_advances');
    }
}
