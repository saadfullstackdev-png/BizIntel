<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('comment')->nullable();

            $table->unsignedInteger('lead_id');
            $table->unsignedInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Manage Foreing Key Relationshops Mapping
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_comments');
    }
}