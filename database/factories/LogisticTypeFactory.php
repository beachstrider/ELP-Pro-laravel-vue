<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\LogisticType;
use Faker\Generator as Faker;

$factory->define(LogisticType::class, function (Faker $faker) {
	return [
		'title' => $faker->company,
		'is_active' => 1,
	];
});
