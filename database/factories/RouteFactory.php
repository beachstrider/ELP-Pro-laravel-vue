<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Route;
use Faker\Generator as Faker;

$factory->define(Route::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'from_location' => $faker->address,
        'to_location' => $faker->address,
        'description' => $faker->paragraph(1),        
    ];
});
