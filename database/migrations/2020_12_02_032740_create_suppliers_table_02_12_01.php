<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable021201 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->char('guid', 36)->unique()->index()->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
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

        Schema::create('supplier_user_types', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedInteger('supplier_type_id')->nullable();
        });

        Schema::create('supplier_transportation_types', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('transportation_type_id')->nullable();
        });

        Schema::create('supplier_locations', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('location_type_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_contact_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('supplier_contact_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
        });

        Schema::create('supplier_contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('supplier_user_types');
        Schema::dropIfExists('supplier_contracts');
        Schema::dropIfExists('supplier_transportation_types');
        Schema::dropIfExists('supplier_locations');
        Schema::dropIfExists('supplier_contacts');
        Schema::dropIfExists('supplier_contact_locations');
    }
}
