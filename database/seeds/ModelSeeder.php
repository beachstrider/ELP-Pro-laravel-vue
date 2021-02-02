<?php

use App\Domain\Models\BrandModel;
use Illuminate\Database\Seeder;

class ModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BrandModel::query()->truncate();

        factory(BrandModel::class, 5)
        ->create([
            'brand_id' => \App\Domain\Models\Brand::query()->where('is_active', 1)->inRandomOrder()->first()
        ]);

        factory(BrandModel::class, 5)
        ->create([
            'brand_id' => \App\Domain\Models\Brand::query()->where('is_active', 1)->inRandomOrder()->first()
        ]);

        factory(BrandModel::class, 5)
        ->create([
            'brand_id' => \App\Domain\Models\Brand::query()->where('is_active', 1)->inRandomOrder()->first()
        ]);
    }
}
