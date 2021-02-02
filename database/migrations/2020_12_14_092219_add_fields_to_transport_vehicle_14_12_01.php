<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToTransportVehicle141201 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('plate_number');
            $table->string('euro_norm')->nullable()->after('brand');
            $table->double('year_of_production')->nullable()->after('capacity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            $table->dropColumn('brand');
            $table->dropColumn('euro_norm');
            $table->dropColumn('year_of_production');
        });
    }
}
