<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoColumnsInDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable()->after('updated_at');
            $table->unsignedInteger('updated_by')->nullable()->after('created_by');

            $table->foreign('created_by', 'discount_created_by_user_id')
                ->references('id')
                ->on('users');

            $table->foreign('updated_by', 'discount_updated_by_user_id')
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
        Schema::table('discounts', function (Blueprint $table) {

            $table->dropForeign('discount_created_by_user_id');
            $table->dropForeign('discount_updated_by_user_id');

            $table->dropColumn('created_by', 'updated_by');
        });
    }
}
