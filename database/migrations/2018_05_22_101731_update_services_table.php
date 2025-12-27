<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('name');
            $table->string('duration')->nullable()->after('parent_id');
            $table->double('price', 11, 2)->default(0.00)->after('duration');
            $table->string('color')->nullable()->after('price');
            $table->unsignedTinyInteger('end_node')->default(0)->after('color');
            $table->unsignedInteger('account_id');

            // Foreign Key Relationships
            $table->foreign('account_id', 'services_account')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('duration');
            $table->dropColumn('price');
            $table->dropColumn('color');
            $table->dropColumn('parent_id');
            $table->dropColumn('end_node');

            $table->dropForeign('services_account');
            $table->dropColumn('account_id');
        });
    }
}
