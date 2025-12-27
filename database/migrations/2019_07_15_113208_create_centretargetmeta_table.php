<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCentretargetmetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('centretargetmeta', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->year('year')->index();
            $table->unsignedInteger('location_id')->index();
            $table->unsignedInteger('centertarget_id')->index();
            $table->decimal('target_amount',12,2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id', 'centretargetmeta_account')
                ->references('id')->on('accounts');
            $table->foreign('location_id', 'centretargetmeta_location')
                ->references('id')->on('locations');
            $table->foreign('centertarget_id', 'centretarget_meta_id')
                ->references('id')->on('centertarget');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('centretargetmeta', function (Blueprint $table) {
            $table->dropForeign('centertarget_account');
            $table->dropColumn('account_id');

            $table->dropForeign('centretargetmeta_location');
            $table->dropColumn('location_id');

            $table->dropForeign('centretarget_meta_id');
            $table->dropColumn('centertarget_id');

            $table->dropIfExists('centretargetmeta');
        });
    }
}
