<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOneColumnPackageAdvances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_advances', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_tax')->after('is_adjustment')->default(0);
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
            $table->dropColumn('is_tax');
        });
    }
}
