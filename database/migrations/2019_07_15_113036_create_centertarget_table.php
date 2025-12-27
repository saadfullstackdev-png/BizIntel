<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCentertargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('centertarget', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->year('year')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id', 'centertarget_account')
                ->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('centertarget', function (Blueprint $table) {
            $table->dropForeign('centertarget_account');
            $table->dropColumn('account_id');
            $table->dropIfExists('staff_targets');
        });
    }
}
