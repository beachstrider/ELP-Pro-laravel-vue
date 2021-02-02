<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Driver;
use Faker\Generator as Faker;

$factory->define(Driver::class, function (Faker $faker) {
    return [
        'is_active' => 1
    ];
});
