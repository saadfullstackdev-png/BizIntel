<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rooms', function (Blueprint $table) {

            $table->unsignedInteger('resource_type_id')->nullable()->after('account_id');
            $table->foreign('resource_type_id', 'rooms_resource_type')->references('id')->on('resource_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign('rooms_resource_type');
            $table->dropColumn('resource_type_id');
        });
    }
}
