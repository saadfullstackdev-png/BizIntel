<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffTargetServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_target_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id')->index();
            $table->unsignedInteger('location_id')->default(1)->index();
            $table->unsignedInteger('staff_target_id')->index();
            $table->unsignedInteger('staff_id')->index();
            $table->unsignedInteger('service_id')->index();
            $table->decimal('target_amount',12,2)->default(0);
            $table->unsignedTinyInteger('target_services')->default(0);
            $table->unsignedTinyInteger('month')->index();
            $table->year('year')->index();
            $table->timestamps();
            $table->softDeletes();

            // Manage Foreing Key Relationshops
            $table->foreign('account_id', 'staff_target_services_account')
                ->references('id')->on('accounts');

            $table->foreign('location_id', 'staff_target_services_location')
                ->references('id')->on('locations');

            $table->foreign('staff_target_id', 'staff_target_services_staff_target')
                ->references('id')->on('staff_targets');

            $table->foreign('staff_id', 'staff_target_services_staff')
                ->references('id')->on('users');

            $table->foreign('service_id', 'staff_target_services_service')
                ->references('id')->on('services');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff_target_services', function (Blueprint $table) {
            $table->dropForeign('staff_target_services_account');
            $table->dropColumn('account_id');

            $table->dropForeign('staff_target_services_location');
            $table->dropColumn('location_id');

            $table->dropForeign('staff_target_services_staff_target');
            $table->dropColumn('staff_target_id');

            $table->dropForeign('staff_target_services_staff');
            $table->dropColumn('staff_id');

            $table->dropForeign('staff_target_services_service');
            $table->dropColumn('service_id');
        });

        Schema::dropIfExists('staff_target_services');
    }
}
