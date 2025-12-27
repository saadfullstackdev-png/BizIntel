<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFourColumnPackageService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_services', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_exclusive')->after('price')->nullable();
            $table->double('tax_exclusive_price', 11, 2)->after('is_exclusive')->default(0.00);
            $table->double('tax_percenatage', 11, 2)->after('tax_exclusive_price')->default(0.00);
            $table->double('tax_price', 11, 2)->after('tax_percenatage')->default(0.00);
            $table->double('tax_including_price', 11, 2)->after('tax_price')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_services', function (Blueprint $table) {
            $table->dropColumn('is_exclusive');
            $table->dropColumn('tax_exclusive_price');
            $table->dropColumn('tax_percenatage');
            $table->dropColumn('tax_price');
            $table->dropColumn('tax_including_price');
        });
    }
}
