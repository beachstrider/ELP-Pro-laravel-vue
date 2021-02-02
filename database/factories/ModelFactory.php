<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\BrandModel;
use Faker\Generator as Faker;

$factory->define(BrandModel::class, function (Faker $faker) {
    return [
        'title' => $faker->company,
        'is_active' => 1,
        'height' => $faker->numberBetween(10, 50),
        'width' => $faker->numberBetween(10, 50),
        'delivery_factors' => $faker->numberBetween(1, 5),
        'type' => '',
        'length' =>  $faker->numberBetween(10, 50),
    ];
});
