<?php

use App\Domain\Models\Driver;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Driver::query()->truncate();
        factory(Driver::class, 3)
        ->create([
            'supplier_id' => User::query()->inRandomOrder()->first()->id,
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'is_active' => 0
        ]);

        factory(Driver::class, 3)
        ->create([
            'supplier_id' => User::query()->inRandomOrder()->first()->id,
            'user_id' => User::query()->inRandomOrder()->first()->id
        ]);

    }
}
