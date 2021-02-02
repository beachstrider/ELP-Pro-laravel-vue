<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'from_opening_hours')) {
                $table->string('from_opening_hours')->nullable()->after('country');
            }
            if (!Schema::hasColumn('locations', 'to_opening_hours')) {
                $table->string('to_opening_hours')->nullable()->after('from_opening_hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropColumn('opening_hours');
    }
}
