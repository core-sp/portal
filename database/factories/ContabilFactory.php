<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Contabil;
use Faker\Generator as Faker;

$factory->define(Contabil::class, function (Faker $faker) {
    $faker->addProvider(new \Faker\Provider\pt_BR\Company($faker));

    return [
        'cnpj' => $faker->cnpj(false),
        'nome' => mb_strtoupper($faker->company, 'UTF-8'),
        'email' => $faker->email,
        'nome_contato' => mb_strtoupper($faker->name, 'UTF-8'),
        'telefone' => '(11) 12345-1234',
        'password' => bcrypt('Teste102030'), 
        'verify_token' => null, 
        'ativo' => 1,
        'aceite' => 1
    ];
});

$factory->state(Contabil::class, 'low', function (Faker $faker) {
    return [
        'nome' => $faker->company,
        'nome_contato' => $faker->name,
    ];
});

$factory->state(Contabil::class, 'cadastro', function (Faker $faker) {
    return [
        'tipo_conta' => 'contabil',
        'aceite' => 'on',
        'password' => 'Teste102030',
        'password_confirmation' => 'Teste102030'
    ];
});

$factory->state(Contabil::class, 'sem_login', function (Faker $faker) {
    return [
        'password' => null, 
        'verify_token' => null, 
        'ativo' => null,
        'aceite' => null
    ];
});

$factory->afterMakingState(Contabil::class, 'sem_login', function ($contabil, $faker) {
    $contabil->makeHidden([
        'password', 
        'verify_token', 
        'ativo',
        'aceite',
        'created_at', 
        'updated_at', 
        'deleted_at',
    ]);
});

$factory->afterCreatingState(Contabil::class, 'sem_login', function ($contabil, $faker) {
    $contabil->makeHidden([
        'password', 
        'verify_token', 
        'ativo',
        'aceite',
        'created_at', 
        'updated_at', 
        'deleted_at',
    ]);
});