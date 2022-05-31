<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Contabil;
use Faker\Generator as Faker;

$factory->define(Contabil::class, function (Faker $faker) {
    return [
        'cnpj' => '78087976000130',
        'nome' => 'Contabil Teste',
        'email' => $faker->email,
        'nome_contato' => 'Dono da Contabil',
        'telefone' => '(11) 12345-1234',
    ];
});
