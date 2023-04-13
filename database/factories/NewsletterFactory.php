<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Newsletter;
use Faker\Generator as Faker;

$factory->define(Newsletter::class, function (Faker $faker) {
    return [
        'nome' => $faker->firstName . ' ' . $faker->lastName,
        'email' => $faker->email,
        'celular' => '11123456789',
    ];
});

$factory->state(Newsletter::class, 'request', [
    'celular' => '(11) 12345-6789',
    'termo' => 'on',
]);