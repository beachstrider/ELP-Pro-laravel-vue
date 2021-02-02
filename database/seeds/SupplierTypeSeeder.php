<?php

use App\Domain\Models\SupplierType;
use Illuminate\Database\Seeder;

class SupplierTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SupplierType::query()->truncate();
        SupplierType::query()->create(['title' => 'Carrier', 'slug' => 'carrier']);
        SupplierType::query()->create(['title' => 'Release Agent', 'slug' => 'release_agent']);
        SupplierType::query()->create(['title' => 'Compound', 'slug' => 'compound']);
    }
}
