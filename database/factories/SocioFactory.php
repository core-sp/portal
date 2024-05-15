<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Socio;
use Faker\Generator as Faker;

$factory->define(Socio::class, function (Faker $faker) {
    $faker->addProvider(new \Faker\Provider\pt_BR\Person($faker));

    return [
        'cpf_cnpj' => $faker->cpf(false),
        'registro' => null,
        'nome' => mb_strtoupper($faker->name, 'UTF-8'),
        'nome_social' => null,
        'dt_nascimento' => now()->subYears(40)->format('Y-m-d'),
        'identidade' => '221111113',
        'orgao_emissor' => 'SSP- SP',
        'cep' => '03021-050',
        'logradouro' => 'RUA TESTE DO SÓCIO PF',
        'numero' => '155',
        'complemento' => null,
        'bairro' => 'TESTE BAIRRO SÓCIO PF',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'nacionalidade' => 'BRASILEIRA',
        'naturalidade_estado' => 'SP',
        'nome_mae' => mb_strtoupper($faker->name, 'utf-8'),
        'nome_pai' => mb_strtoupper($faker->name, 'utf-8'),
    ];
});

$factory->state(Socio::class, 'pj', function (Faker $faker) {
    $faker->addProvider(new \Faker\Provider\pt_BR\Company($faker));

    return [
        'cpf_cnpj' => $faker->cnpj(false),
        'nome' => mb_strtoupper($faker->company, 'UTF-8'),
        'nome_social' => null,
        'dt_nascimento' => null,
        'identidade' => null,
        'orgao_emissor' => null,
        'cep' => '03028-040',
        'logradouro' => 'RUA TESTE DO SÓCIO PJ',
        'numero' => '15',
        'complemento' => null,
        'bairro' => 'TESTE BAIRRO SÓCIO PJ',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'nacionalidade' => null,
        'naturalidade_estado' => null,
        'nome_mae' => null,
        'nome_pai' => null,
    ];
});