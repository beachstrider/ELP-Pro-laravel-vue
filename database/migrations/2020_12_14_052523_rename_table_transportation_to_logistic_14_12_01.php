<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTableTransportationToLogistic141201 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('transportation_types', 'logistic_types');
        Schema::rename('supplier_transportation_types', 'supplier_logistic_types');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('logistic_types', 'transportation_types');
        Schema::rename('supplier_logistic_types', 'supplier_transportation_types');
    }
}
