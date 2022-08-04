<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistro;
use Faker\Generator as Faker;

$factory->define(PreRegistro::class, function (Faker $faker) {
    return [
        'segmento' => mb_strtoupper(segmentos()[5], 'UTF-8'),
        'registro_secundario' => null,
        'cep' => '01234-001',
        'logradouro' => 'RUA TESTE DA ESQUINA',
        'numero' => '29',
        'complemento' => null,
        'bairro' => 'TESTE',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'telefone' => '(11) 00000-0000',
        'tipo_telefone' => mb_strtoupper(tipos_contatos()[0], 'UTF-8'),
        'opcional_celular' => mb_strtoupper(opcoes_celular()[1], 'UTF-8'),
        'user_externo_id' => factory('App\UserExterno'),
        'contabil_id' => factory('App\Contabil'),
        'idregional' => factory('App\Regional'),
        'idusuario' => factory('App\User'),
        'status' => PreRegistro::STATUS_CRIADO,
        'justificativa' => null,
        'confere_anexos' => null,
        'historico_contabil' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
        'historico_status' => json_encode([PreRegistro::STATUS_CRIADO . ';' . now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
        'campos_espelho' => null,
        'campos_editados' => null,
    ];
});

$factory->state(PreRegistro::class, 'low', function (Faker $faker) {
    return [
        'segmento' => segmentos()[5],
        'tipo_telefone' => tipos_contatos()[0],
        'opcional_celular' => opcoes_celular()[1] . ',' . opcoes_celular()[0],
    ];
});

$factory->state(PreRegistro::class, 'pj', function (Faker $faker) {
    return [
        'user_externo_id' => factory('App\UserExterno')->states('pj'),
    ];
});

$factory->state(PreRegistro::class, 'analise_inicial', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_ANALISE_INICIAL,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
        'idusuario' => null,
    ];
});

$factory->state(PreRegistro::class, 'enviado_correcao', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_CORRECAO,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
    ];
});

$factory->state(PreRegistro::class, 'analise_correcao', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_ANALISE_CORRECAO,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_CORRECAO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
    ];
});

$factory->state(PreRegistro::class, 'aprovado', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_APROVADO,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_CORRECAO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_APROVADO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
    ];
});

$factory->state(PreRegistro::class, 'negado', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_NEGADO,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_CORRECAO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_NEGADO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
    ];
});
