<?php

use App\Domain\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Route::query()->truncate();
        factory(Route::class, 8)->create();
    }
}
