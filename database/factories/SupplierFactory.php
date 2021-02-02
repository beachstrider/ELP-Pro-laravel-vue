<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Supplier;
use Faker\Generator as Faker;

$factory->define(Supplier::class, function (Faker $faker) {
    $firstName = $faker->firstName;
    $lastName = $faker->lastName;

    return [
        'name' => "{$firstName} {$lastName}",
        'first_name' => $firstName,
        'last_name' => $lastName,
        'phone' => $faker->phoneNumber,
        'email' => $faker->safeEmail,
        'fax' => $faker->phoneNumber,
        'comment' => $faker->paragraph(2),
        'identification_number' => str_random(10),
        'is_active' => 1,
    ];
});
