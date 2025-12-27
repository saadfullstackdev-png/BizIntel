<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoColumnDiscounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->string('discount_type')->after('description')->nullable();
            $table->integer('pre_days')->after('discount_type')->default(0);
            $table->integer('post_days')->after('pre_days')->default(0);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->dropColumn('pre_days');
            $table->dropColumn('post_days');
        });
    }
}
