<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Contabil;
use Faker\Generator as Faker;

$factory->define(Contabil::class, function (Faker $faker) {
    return [
        'cnpj' => '78087976000130',
        'nome' => mb_strtoupper($faker->company, 'UTF-8'),
        'email' => mb_strtoupper($faker->email, 'UTF-8'),
        'nome_contato' => mb_strtoupper($faker->name, 'UTF-8'),
        'telefone' => '(11) 12345-1234',
    ];
});

$factory->state(Contabil::class, 'low', function (Faker $faker) {
    return [
        'nome' => $faker->company,
        'email' => $faker->email,
        'nome_contato' => $faker->name,
    ];
});
