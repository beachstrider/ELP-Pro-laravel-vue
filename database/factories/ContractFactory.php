<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Contract;
use Faker\Generator as Faker;

$factory->define(Contract::class, function (Faker $faker) {
    $duration = ['1 year', '2 year', '3 year'];

    return [
        'title' => $faker->company,
        'duration' => $duration[rand(0,2)],
        'description' => $faker->paragraph(1),
        'start_date' => $faker->dateTimeBetween('now'),
        'end_date' => $faker->dateTimeBetween('now', '+30 days')
    ];
});
