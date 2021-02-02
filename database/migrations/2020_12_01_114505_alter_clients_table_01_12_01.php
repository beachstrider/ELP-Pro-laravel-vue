<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterClientsTable011201 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->dropColumn('location_type_id');
        });

        Schema::dropIfExists('client_locations');

        Schema::create('client_locations', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('location_type_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('location_type_id')->after('contact_id')->nullable();
        });

        Schema::dropIfExists('client_locations');

        Schema::create('client_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
        });
    }
}
