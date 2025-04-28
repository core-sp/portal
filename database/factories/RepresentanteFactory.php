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
        'ultimo_acesso' => now()->subDay()->subHour()->subMinute()->format('Y-m-d h:i:s'),
        'created_at' => now(),
        'updated_at' => now()
    ];
});

$factory->state(Representante::class, 'irregular', function ($faker) {
    return [
        'cpf_cnpj' => '68126712589',
        'registro_core' => '0000000004',
        'ass_id' => '000004',
    ];
});

$factory->state(Representante::class, 'cancelado', function ($faker) {
    return [
        'cpf_cnpj' => '22553674830',
        'registro_core' => '0000000005',
        'ass_id' => '000005',
    ];
});

$factory->state(Representante::class, 'sem_segmento', function ($faker) {
    return [
        'cpf_cnpj' => '56983238010',
        'registro_core' => '0000000003',
        'ass_id' => '000003',
    ];
});
