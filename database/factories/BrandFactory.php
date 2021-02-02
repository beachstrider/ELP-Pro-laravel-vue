<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Brand;
use Faker\Generator as Faker;

$factory->define(Brand::class, function (Faker $faker) {
    return [
        'title' => $faker->company,
        'is_active' => 1,
    ];
});
