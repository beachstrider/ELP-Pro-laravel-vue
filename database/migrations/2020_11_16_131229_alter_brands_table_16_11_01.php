<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBrandsTable161101 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brands', function (Blueprint $table){
            $table->dropColumn('type');
            $table->dropColumn('length');
            $table->dropColumn('width');
            $table->dropColumn('delivery_factors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table){
            $table->string('type', 20)->nullable()->after('title');
            $table->string('length', 50)->nullable()->after('type');
            $table->string('width', 50)->nullable()->after('length');
            $table->string('delivery_factors')->nullable();
        });
    }
}
