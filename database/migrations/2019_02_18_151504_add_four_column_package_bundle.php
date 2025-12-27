<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFourColumnPackageBundle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_bundles', function (Blueprint $table) {

            $table->double('orignal_price', 11, 2)->default(0.00);

            $table->unsignedTinyInteger('is_exclusive')->after('net_amount')->nullable();
            $table->double('tax_exclusive_net_amount', 11, 2)->after('is_exclusive')->default(0.00);
            $table->double('tax_percenatage', 11, 2)->after('tax_exclusive_net_amount')->default(0.00);
            $table->double('tax_price', 11, 2)->after('tax_percenatage')->default(0.00);
            $table->double('tax_including_price', 11, 2)->after('tax_price')->default(0.00);
            $table->unsignedInteger('location_id')->nullable()->after('tax_including_price');
            $table->foreign('location_id', 'package_bundle_location_id')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_bundles', function (Blueprint $table) {
            $table->dropColumn('is_exclusive');
            $table->dropColumn('tax_percenatage');
            $table->dropColumn('tax_exclusive_net_amount');
            $table->dropColumn('tax_price');
            $table->dropColumn('tax_including_price');

            $table->dropForeign('package_bundle_location_id');
            $table->dropColumn('location_id');

        });
    }
}
