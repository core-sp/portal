<?php

use App\UserExterno;
use Faker\Generator as Faker;

$factory->define(UserExterno::class, function (Faker $faker) {
    return [
        'cpf_cnpj' => '86294373085', 
        'nome' => strtoupper($faker->name),
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt('Teste102030'), 
        'verify_token' => null, 
        'ativo' => 1,
        'aceite' => 1
    ];
});
