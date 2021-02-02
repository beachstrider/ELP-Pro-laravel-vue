<?php

use Illuminate\Database\Seeder;
use App\Domain\Models\Location;
use App\Domain\Models\LocationType;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')->truncate();
        $path = base_path() . '/database/seeds/countries.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);

        Location::query()->truncate();
        factory(Location::class, 12)->create();
    }
}
