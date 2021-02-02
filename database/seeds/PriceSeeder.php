<?php

use Illuminate\Database\Seeder;
use App\Domain\Models\Price;
use App\Domain\Models\User;
use App\Domain\Models\Route;
use App\Domain\Models\Brand;
use App\Domain\Models\BrandModel;
use App\Domain\Models\LogisticType;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Price::query()->truncate();
        $brands = Brand::query()->whereHas('models')->inRandomOrder()->limit(5)->get();

        $brands
        ->map(function($item) {
            factory(Price::class, 3)
                ->create([
                    'supplier_id' => User::query()->inRandomOrder()->first()->id,
                    'route_id' => Route::query()->inRandomOrder()->first()->id,
                    'brand_id' => $item->id,
                    'model_id' => BrandModel::query()->where('brand_id', $item->id)->inRandomOrder()->first()->id,
                    'logistic_type_id' => LogisticType::query()->inRandomOrder()->first()->id,
                ]);
        });
    }
}
