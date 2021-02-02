<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealersTable251101 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id()->index();
            $table->char('guid', 36)->unique()->index()->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('main_location_id')->nullable();
            $table->string('dealer_id')->unique();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->longText('comment');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dealer_additional_locations', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->unsignedBigInteger('location_type_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dealer_contacts', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dealer_contact_locations', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->unsignedBigInteger('dealer_contact_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dealer_brands', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dealer_brand_models', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->unsignedBigInteger('dealer_brand_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
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
        Schema::dropIfExists('dealers');
        Schema::dropIfExists('dealer_additional_locations');
        Schema::dropIfExists('dealer_contacts');
        Schema::dropIfExists('dealer_contact_locations');
        Schema::dropIfExists('dealer_brands');
        Schema::dropIfExists('dealer_brand_models');
    }
}


