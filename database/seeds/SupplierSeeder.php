<?php

use Illuminate\Database\Seeder;
use App\Domain\Models\Supplier;
use App\Domain\Models\SupplierContact;
use App\Domain\Models\SupplierLocation;
use App\Domain\Models\LogisticType;
use App\Domain\Models\Location;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        Supplier::query()->truncate();
        SupplierContact::query()->truncate();
        SupplierLocation::query()->truncate();
        DB::table('supplier_contact_locations')->truncate();
        DB::table('supplier_transportation_types')->truncate();
        DB::table('supplier_user_types')->truncate();
        factory(Supplier::class, 2)
            ->create()
            ->each(function($supplier) {
                $supplier->main_location_id = Location::query()->inRandomOrder()->first()->id;
                $supplier->supplierTransportationTypes()->attach(LogisticType::query()->inRandomOrder()->limit(2)->get()->pluck('id')->toArray());
                $supplier->supplierUserTypes()->attach([1, 2]);
                $supplier->save();
            });

        DB::commit();
    }
}
