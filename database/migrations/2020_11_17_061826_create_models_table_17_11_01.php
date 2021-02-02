<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelsTable171101 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('models', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->char('guid', 36)->unique()->index()->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->string('delivery_factors')->nullable();
            $table->double('length')->nullable();
            $table->double('height')->nullable();
            $table->double('width')->nullable();
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
        Schema::dropIfExists('models');
    }
}
