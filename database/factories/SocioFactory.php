<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Socio;
use App\ResponsavelTecnico;
use App\PreRegistro;
use Faker\Generator as Faker;

$factory->define(Socio::class, function (Faker $faker) {
    $faker->addProvider(new \Faker\Provider\pt_BR\Person($faker));

    return [
        'cpf_cnpj' => $faker->cpf(false),
        'registro' => null,
        'nome' => str_replace("'", "", mb_strtoupper($faker->name, 'UTF-8')),
        'nome_social' => str_replace("'", "", mb_strtoupper($faker->name, 'UTF-8')),
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

$factory->afterMakingState(Socio::class, 'pj', function ($socio, $faker) {
    $socio->makeHidden(['nome_social', 'dt_nascimento', 'identidade', 'orgao_emissor', 'nacionalidade', 'naturalidade_estado', 'nome_mae', 'nome_pai']);
});

$factory->state(Socio::class, 'rt', function (Faker $faker) {
    $rt = ResponsavelTecnico::count() > 0 ? ResponsavelTecnico::orderBy('id', 'DESC')->first()->cpf : factory('App\ResponsavelTecnico')->create()->cpf;
    PreRegistro::orderBy('id', 'DESC')->first()->pessoaJuridica->update(['responsavel_tecnico_id' => ResponsavelTecnico::count()]);

    return [
        'cpf_cnpj' => $rt,
        'nome' => null,
        'nome_social' => null,
        'dt_nascimento' => null,
        'identidade' => null,
        'orgao_emissor' => null,
        'cep' => null,
        'logradouro' => null,
        'numero' => null,
        'complemento' => null,
        'bairro' => null,
        'cidade' => null,
        'uf' => null,
        'nacionalidade' => 'BRASILEIRA',
        'naturalidade_estado' => 'MG',
        'nome_mae' => null,
        'nome_pai' => null,
    ];
});

$factory->afterMakingState(Socio::class, 'rt', function ($socio, $faker) {
    $socio->makeHidden(['nome', 'nome_social', 'dt_nascimento', 'identidade', 'orgao_emissor', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 
    'cidade', 'uf', 'nome_mae', 'nome_pai']);
});

$factory->state(Socio::class, 'low', function (Faker $faker) {
    return [];
});

$factory->afterMakingState(Socio::class, 'low', function ($socio, $faker) {
    foreach(['nome', 'nome_social', 'orgao_emissor', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'nome_mae', 'nome_pai'] as $campo)
        $socio[$campo] = !isset($socio[$campo]) ? null : mb_strtolower($socio[$campo], 'UTF-8');

    if($socio->socioPF())
        $socio['nacionalidade'] = 'Brasileira';
});