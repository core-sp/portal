<?php

use App\Representante;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

$factory->define(Representante::class, function (Faker $faker) {
    return [
        'cpf_cnpj' => '86294373085', 
        'registro_core' => '0000000001', 
        'ass_id' => '000001', 
        'nome' => 'RC Teste 1', 
        'email' => 'desenvolvimento@core-sp.org.br', 
        'password' => bcrypt('teste102030'), 
        'verify_token' => null, 
        'aceite' => 1, 
        'ativo' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ];
});
