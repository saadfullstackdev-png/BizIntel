<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelecomprovidernumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telecomprovidernumbers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pre_fix',500);
            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedInteger('telecomprovider_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('telecomprovider_id')->references('id')->on('telecomproviders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telecomprovidernumbers');
    }
}
