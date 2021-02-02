<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnSupplierLogisticTypesTable141201 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplier_logistic_types', function (Blueprint $table) {
            $table->dropColumn('transportation_type_id');
            $table->unsignedBigInteger('logistic_type_id')->after('supplier_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplier_logistic_types', function (Blueprint $table) {
            $table->dropColumn('logistic_type_id');
            $table->unsignedBigInteger('transportation_type_id')->after('supplier_id')->nullable();
        });
    }
}
