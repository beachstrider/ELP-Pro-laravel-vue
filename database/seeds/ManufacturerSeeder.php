<?php

use Illuminate\Database\Seeder;
use App\Domain\Models\Manufacturer;
use App\Domain\Models\ManufacturerContact;
use App\Domain\Models\ManufacturerBrand;
use App\Domain\Models\ManufacturerLocation;
use App\Domain\Models\Location;
use Illuminate\Support\Facades\DB;

class ManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        Manufacturer::query()->truncate();
        ManufacturerContact::query()->truncate();
        ManufacturerBrand::query()->truncate();
        ManufacturerLocation::query()->truncate();
        DB::table('manufacturer_brand_models')->truncate();
        DB::table('manufacturer_contact_locations')->truncate();
        DB::table('manufacturer_location_brands')->truncate();
        DB::table('manufacturer_location_brand_models')->truncate();
        DB::table('manufacturer_location_suppliers')->truncate();
        factory(Manufacturer::class, 2)
        ->create()
        ->each(function($manufacturer) {
            $manufacturer->main_location_id = Location::query()->inRandomOrder()->first()->id;
            $manufacturer->save();
        });

        DB::commit();
    }
}

