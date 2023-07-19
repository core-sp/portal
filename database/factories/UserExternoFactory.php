<?php

use App\UserExterno;
use Faker\Generator as Faker;

$factory->define(UserExterno::class, function (Faker $faker) {
    return [
        'cpf_cnpj' => '28819854082', 
        'nome' => mb_strtoupper($faker->name, 'UTF-8'),
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt('Teste102030'), 
        'verify_token' => null, 
        'ativo' => 1,
        'aceite' => 1
    ];
});

$factory->state(UserExterno::class, 'pj', function (Faker $faker) {
    return [
        'cpf_cnpj' => '06985713000138'
    ];
});

$factory->state(UserExterno::class, 'cadastro', function (Faker $faker) {
    return [
        'tipo_conta' => 'user_externo',
        'aceite' => 'on',
        'password' => 'Teste102030',
        'password_confirmation' => 'Teste102030'
    ];
});