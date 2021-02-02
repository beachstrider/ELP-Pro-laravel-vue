<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */


use App\Domain\Models\TransportVehicle;
use Faker\Generator as Faker;

$factory->define(TransportVehicle::class, function (Faker $faker) {

    $type = ['air', 'land', 'rail', 'road', 'water', 'other means'];

    return [
    	'capacity' => rand(1, 8),
        'title' => $faker->company,
        'brand' => $faker->company,
        'euro_norm' => 'lorem',
        'year_of_production' => $faker->year,
        'type' => $type[rand(0,2)],
        'plate_number' => $faker->regexify('[A-Za-z0-9]{10}')
    ];
});
