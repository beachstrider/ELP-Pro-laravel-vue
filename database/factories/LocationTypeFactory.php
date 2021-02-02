<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\LocationType;
use Faker\Generator as Faker;

$factory->define(LocationType::class, function (Faker $faker) {
    return [
        'title' => $faker->company,
        'is_active' => 1,
    ];
});
