<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Location;
use Faker\Generator as Faker;

$factory->define(Location::class, function (Faker $faker) {
    return [
        'street' => $faker->streetAddress,
        'street_no' => 50,
        'zip' => '3185',
        'city' => $faker->city,
        'country' => 'Switzerland',
        'from_opening_hours' => $faker->randomDigit,
        'to_opening_hours' => $faker->randomDigit,
    ];
});
