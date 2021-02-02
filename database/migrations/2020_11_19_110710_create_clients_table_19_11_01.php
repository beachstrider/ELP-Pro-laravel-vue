<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable191101 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->char('guid', 36)->unique()->index()->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('main_location_id')->nullable();
            $table->tinyInteger('is_active')->default(false);
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('fax')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
        });

        Schema::create('client_contacts', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('location_type_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_contact_locations', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('client_contact_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
        });

        Schema::create('client_dealers', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('dealer_id')->nullable();
        });

        Schema::create('client_brands', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_brand_models', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('client_brand_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_brands');
        Schema::dropIfExists('client_brand_models');
        Schema::dropIfExists('client_contacts');
        Schema::dropIfExists('client_contact_locations');
        Schema::dropIfExists('client_dealers');
        Schema::dropIfExists('client_locations');
    }
}