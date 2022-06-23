<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCnpj;
use Faker\Generator as Faker;

$factory->define(PreRegistroCnpj::class, function (Faker $faker) {
    return [
        'razao_social' => mb_strtoupper($faker->company, 'UTF-8'),
        'nire' => '123456789',
        'tipo_empresa' => tipos_empresa()[0],
        'dt_inicio_atividade' => '2020-12-21',
        'inscricao_municipal' => '123456789',
        'inscricao_estadual' => '987654321',
        'capital_social' => '1.000,00',
        'cep' => '01234-050',
        'logradouro' => 'RUA TESTE DA RUA',
        'numero' => '25A',
        'complemento' => null,
        'bairro' => 'TESTE BAIRRO',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'responsavel_tecnico_id' => factory('App\ResponsavelTecnico'),
        'pre_registro_id' => factory('App\PreRegistro'),
    ];
});
