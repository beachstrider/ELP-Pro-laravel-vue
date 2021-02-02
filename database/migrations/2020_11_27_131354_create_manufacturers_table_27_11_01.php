<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufacturersTable271101 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->char('guid', 36)->unique()->index()->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('main_location_id')->nullable();
            $table->tinyInteger('is_active')->default(false);
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('fax')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('manufacturer_contacts', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('manufacturer_contact_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('manufacturer_contact_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
        });

        Schema::create('manufacturer_brands', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('manufacturer_brand_models', function (Blueprint $table) {
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('manufacturer_brand_id')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
        });

        Schema::create('manufacturer_locations', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('location_type_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('manufacturer_location_brands', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('manufacturer_location_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('manufacturer_location_brand_models', function (Blueprint $table) {
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('manufacturer_location_id')->nullable();
            $table->unsignedBigInteger('manufacturer_location_brand_id')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
        });

        Schema::create('manufacturer_location_suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('manufacturer_location_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacturers');
        Schema::dropIfExists('manufacturer_contacts');
        Schema::dropIfExists('manufacturer_locations');
        Schema::dropIfExists('manufacturer_contact_locations');
        Schema::dropIfExists('manufacturer_brands');
        Schema::dropIfExists('manufacturer_brand_models');
        Schema::dropIfExists('manufacturer_location_brands');
        Schema::dropIfExists('manufacturer_location_brand_models');
        Schema::dropIfExists('manufacturer_location_suppliers');
    }
}

