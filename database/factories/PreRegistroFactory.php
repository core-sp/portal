<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistro;
use Faker\Generator as Faker;

$factory->define(PreRegistro::class, function (Faker $faker) {
    return [
        'ramo_atividade' => 'Teste Ramo de Atividade',
        'segmento' => segmentos()[5],
        'registro_secundario' => null,
        'cep' => '01234-001',
        'logradouro' => 'Rua teste da esquina',
        'numero' => '29',
        'complemento' => null,
        'bairro' => 'Teste',
        'cidade' => 'São Paulo',
        'uf' => 'SP',
        'telefone' => '(11) 00000-0000',
        'tipo_telefone' => tipos_contatos()[0],
        'user_externo_id' => factory('App\UserExterno'),
        'contabil_id' => factory('App\Contabil'),
        'idregional' => factory('App\Regional'),
        'idusuario' => factory('App\User'),
        'status' => null,
        'justificativa' => null
    ];
});
