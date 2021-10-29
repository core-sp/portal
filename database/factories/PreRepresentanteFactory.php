<?php

use App\PreRepresentante;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

$factory->define(PreRepresentante::class, function (Faker $faker) {
    return [
        'cpf_cnpj' => '86294373085', 
        'nome' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt('Teste102030'), 
        'verify_token' => null, 
        'ativo' => 1
    ];
});
