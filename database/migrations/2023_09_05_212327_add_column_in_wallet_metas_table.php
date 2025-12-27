<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInWalletMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_metas', function (Blueprint $table) {
            $table->integer('is_refund_return')->after('transaction_id')->default(0);
            $table->integer('is_reverse_return')->after('is_refund_return')->default(0);
            $table->unsignedInteger('wallet_meta_id')->after('is_reverse_return')->nullable();
            $table->unsignedInteger('package_id')->after('wallet_meta_id')->nullable();

            $table->foreign('package_id')
                ->references('id')
                ->on('packages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_metas', function (Blueprint $table) {
            $table->dropColumn('is_refund_return');
            $table->dropColumn('is_reverse_return');
            $table->dropColumn('wallet_meta_id');
            $table->dropColumn('package_id');
        });
    }
}
