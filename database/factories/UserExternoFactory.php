<?php

use App\UserExterno;
use Faker\Generator as Faker;

$factory->define(UserExterno::class, function (Faker $faker) {
    $faker->addProvider(new \Faker\Provider\pt_BR\Person($faker));

    return [
        'cpf_cnpj' => $faker->cpf(false), 
        'nome' => mb_strtoupper($faker->name, 'UTF-8'),
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt('Teste102030'), 
        'verify_token' => null, 
        'ativo' => 1,
        'aceite' => 1
    ];
});

$factory->state(UserExterno::class, 'pj', function (Faker $faker) {
    $faker->addProvider(new \Faker\Provider\pt_BR\Company($faker));

    return [
        'cpf_cnpj' => $faker->cnpj(false),
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

$factory->state(UserExterno::class, 'cadastro_by_contabil', function (Faker $faker) {
    return [
        'password' => null, 
        'verify_token' => null, 
        'ativo' => 0,
        'aceite' => 0
    ];
});

$factory->afterMakingState(UserExterno::class, 'cadastro_by_contabil', function ($externo, $faker) {
    $externo->makeHidden([
        'password', 'verify_token', 'ativo', 'aceite',
    ]);
});