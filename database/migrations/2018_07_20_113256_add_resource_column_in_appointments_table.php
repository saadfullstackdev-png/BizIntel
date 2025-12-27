<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResourceColumnInAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("appointments", function (Blueprint $table){
            $table->unsignedInteger("resource_id")->nullable()->after('service_id');

            $table->foreign("resource_id","appointments_resource_id")
                ->references("id")
                ->on("resources");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("appointments", function (Blueprint $table){
            $table->dropForeign("appointments_resource_id");
            $table->dropColumn("resource_id");
        });
    }
}
