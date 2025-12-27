<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_targets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id')->index();
			$table->unsignedInteger('staff_id')->index();
            $table->unsignedInteger('location_id')->default(1)->index();
            $table->decimal('total_amount',12,2)->default(0);
            $table->unsignedTinyInteger('total_services')->default(0);
            $table->unsignedTinyInteger('month')->index();
            $table->year('year')->index();
            $table->timestamps();
            $table->softDeletes();

            // Manage Foreing Key Relationshops
            $table->foreign('account_id', 'staff_targets_account')
                ->references('id')->on('accounts');
			$table->foreign('staff_id', 'staff_targets_staff')
                ->references('id')->on('users');
            $table->foreign('location_id', 'staff_targets_location')
                ->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff_targets', function (Blueprint $table) {
            $table->dropForeign('staff_targets_account');
            $table->dropColumn('account_id');

            $table->dropForeign('staff_targets_staff');
            $table->dropColumn('staff_id');

            $table->dropForeign('staff_targets_location');
            $table->dropColumn('location_id');

            $table->dropIfExists('staff_targets');
        });
    }
}
