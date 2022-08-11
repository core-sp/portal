<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ResponsavelTecnico;
use Faker\Generator as Faker;

$factory->define(ResponsavelTecnico::class, function (Faker $faker) {
    return [
        'cpf' => '47662011089',
        'registro' => null,
        'nome' => mb_strtoupper($faker->name, 'UTF-8'),
        'nome_social' => null,
        'sexo' => 'F',
        'dt_nascimento' => '1970-02-20',
        'cep' => '03021-050',
        'logradouro' => 'RUA TESTE DO RT',
        'numero' => '155',
        'complemento' => null,
        'bairro' => 'TESTE BAIRRO',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'nome_mae' => mb_strtoupper($faker->name, 'utf-8'),
        'nome_pai' => null,
        'tipo_identidade' => mb_strtoupper(tipos_identidade()[0], 'UTF-8'),
        'identidade' => '221111113',
        'orgao_emissor' => 'SSP- SP',
        'dt_expedicao' => '2021-05-20',
    ];
});

$factory->state(ResponsavelTecnico::class, 'low', function (Faker $faker) {
    return [
        'tipo_identidade' => tipos_identidade()[0],
    ];
});