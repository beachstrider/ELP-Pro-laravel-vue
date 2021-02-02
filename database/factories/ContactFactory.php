<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Domain\Models\Contact;

$factory->define(Contact::class, function (Faker $faker) {
    $firstName = $faker->firstName;
    $lastName = $faker->lastName;

    return [
        'name' => "{$firstName} {$lastName}",
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $faker->email,
        'phone' => $faker->phoneNumber,
        'mobile' => $faker->phoneNumber,
        'functions' => $faker->paragraph(1),
    ];
});
