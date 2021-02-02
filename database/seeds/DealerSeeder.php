<?php

use App\Domain\Models\Dealer;
use Illuminate\Database\Seeder;
use App\Domain\Models\DealerAdditionalLocation;
use App\Domain\Models\DealerBrand;
use Illuminate\Support\Facades\DB;
use App\Domain\Models\DealerContact;

class DealerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Dealer::query()->truncate();
        DealerAdditionalLocation::query()->truncate();
        DealerBrand::query()->truncate();
        DealerContact::query()->truncate();
        DB::table('dealer_brand_models')->truncate();
        DB::table('dealer_contact_locations')->truncate();

        factory(Dealer::class, 3)
        ->create([
            'is_active' => 0,
            'main_location_id' => \App\Domain\Models\Location::query()->inRandomOrder()->first()->id
        ]);

        factory(Dealer::class, 3)
        ->create([
            'is_active' => 1,
            'main_location_id' => \App\Domain\Models\Location::query()->inRandomOrder()->first()->id
        ]);
    }
}