<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Domain\Models\SupplierType;

class CreateSupplierTypesTable021201 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_types', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        SupplierType::query()->create(['title' => 'Transport', 'slug' => 'transport']);
        SupplierType::query()->create(['title' => 'Release Agent', 'slug' => 'release_agent']);
        SupplierType::query()->create(['title' => 'Compound', 'slug' => 'compound']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_types');
    }
}
