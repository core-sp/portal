<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Contabil;
use Faker\Generator as Faker;

$factory->define(Contabil::class, function (Faker $faker) {
    return [
        'cnpj' => '78087976000130',
        'nome' => $faker->company,
        'email' => $faker->email,
        'nome_contato' => $faker->name,
        'telefone' => '(11) 12345-1234',
    ];
});
