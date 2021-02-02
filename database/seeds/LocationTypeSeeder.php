<?php

use App\Domain\Models\LocationType;
use Illuminate\Database\Seeder;

class LocationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LocationType::query()->truncate();
        factory(LocationType::class, 8)->create();
        factory(LocationType::class,8)->create(['is_active' => 0]);
    }
}
