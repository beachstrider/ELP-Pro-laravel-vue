<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Client;
use Faker\Generator as Faker;

$factory->define(Client::class, function (Faker $faker) {
    return [
        'identification_number' => str_random(10),
        'company_name' => $faker->company,
        'phone' => $faker->phoneNumber,
        'email' => $faker->safeEmail,
        'fax' => $faker->phoneNumber,
        'comment' => $faker->paragraph(2),
        'is_active' => 1,
    ];
});
