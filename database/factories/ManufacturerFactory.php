<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Manufacturer;
use Faker\Generator as Faker;

$factory->define(Manufacturer::class, function (Faker $faker) {
    $company = $faker->company;
    return [
        'name' => $company,
        'first_name' => $company,
        'phone' => $faker->phoneNumber,
        'email' => $faker->safeEmail,
        'fax' => $faker->phoneNumber,
        'comment' => $faker->paragraph(2),
        'is_active' => 1,
    ];
});

