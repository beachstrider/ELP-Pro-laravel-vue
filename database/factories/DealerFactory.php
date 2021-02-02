<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Dealer;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Dealer::class, function (Faker $faker) {
    return [
        'dealer_id' => Str::random(10),
        'name' => $faker->name,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->phoneNumber,
        'fax' => $faker->phoneNumber,
    	'comment' => $faker->paragraph(1),
        'is_active' => 1
    ];
});
