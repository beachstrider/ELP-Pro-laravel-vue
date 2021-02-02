<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Price;
use Faker\Generator as Faker;

$factory->define(Price::class, function (Faker $faker) {
    return [
        'leading_factors' => $faker->numberBetween(1, 5),
        'lead_time_pickup' => $faker->numberBetween(10, 50),
        'lead_time_transport' => $faker->numberBetween(10, 50),
        'full_loaded_price' => $faker->numberBetween(1000, 10000),
        'single_loaded_price' => $faker->numberBetween(100, 1000),
    ];
});
