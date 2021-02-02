<?php

use App\Domain\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Brand::query()->truncate();
        factory(Brand::class, 8)->create();
        factory(Brand::class, 8)->create(['is_active' => 0]);
    }
}
