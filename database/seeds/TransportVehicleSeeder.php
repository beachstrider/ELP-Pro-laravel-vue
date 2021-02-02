<?php

use App\Domain\Models\TransportVehicle;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;

class TransportVehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TransportVehicle::query()->truncate();
        factory(TransportVehicle::class, 3)->create(['supplier_id' => User::query()->inRandomOrder()->first()->id]);
        factory(TransportVehicle::class, 3)->create(['supplier_id' => User::query()->inRandomOrder()->first()->id]);
        factory(TransportVehicle::class, 3)->create(['supplier_id' => User::query()->inRandomOrder()->first()->id]);
        factory(TransportVehicle::class, 3)->create(['supplier_id' => User::query()->inRandomOrder()->first()->id]);
    }
}
