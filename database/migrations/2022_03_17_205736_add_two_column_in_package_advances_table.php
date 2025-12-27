<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoColumnInPackageAdvancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_advances', function (Blueprint $table) {
            $table->unsignedInteger('transaction_id')->after('payment_mode_id')->nullable();
            $table->unsignedInteger('wallet_id')->after('transaction_id')->nullable();

            // foreign key reference
            $table->foreign('transaction_id', 'package_advances_transaction')->references('id')->on('transactions');
            $table->foreign('wallet_id', 'package_advances_wallet')->references('id')->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_advances', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
            $table->dropForeign('package_advances_transaction');
            $table->dropColumn('wallet_id');
            $table->dropForeign('package_advances_wallet');
        });
    }
}
