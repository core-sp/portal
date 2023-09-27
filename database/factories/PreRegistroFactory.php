<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistro;
use App\UserExterno;
use App\Contabil;
use Faker\Generator as Faker;

$factory->define(PreRegistro::class, function (Faker $faker) {
    $totalSegmentos = count(segmentos()) - 1;
    return [
        'segmento' => mb_strtoupper(segmentos()[$faker->numberBetween(0, $totalSegmentos)], 'UTF-8'),
        'registro_secundario' => null,
        'cep' => '01234-001',
        'logradouro' => 'RUA TESTE DA ESQUINA',
        'numero' => (string) $faker->numberBetween(1, 5000),
        'complemento' => null,
        'bairro' => 'TESTE',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'telefone' => '(11) 00000-0000',
        'tipo_telefone' => mb_strtoupper(tipos_contatos()[0], 'UTF-8'),
        'opcional_celular' => mb_strtoupper(opcoes_celular()[1], 'UTF-8'),
        'user_externo_id' => UserExterno::count() > 0 ? UserExterno::count() : factory('App\UserExterno'),
        'contabil_id' => Contabil::count() > 0 ? Contabil::count() : factory('App\Contabil')->states('sem_login'),
        'idregional' => factory('App\Regional'),
        'idusuario' => factory('App\User'),
        'status' => PreRegistro::STATUS_CRIADO,
        'justificativa' => null,
        'confere_anexos' => null,
        'historico_contabil' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
        'historico_status' => json_encode([PreRegistro::STATUS_CRIADO . ';' . now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
        'historico_justificativas' => null,
        'campos_espelho' => null,
        'campos_editados' => null,
    ];
});

$factory->state(PreRegistro::class, 'low', function (Faker $faker) {
    $totalSegmentos = count(segmentos()) - 1;
    return [
        'segmento' => segmentos()[$faker->numberBetween(0, $totalSegmentos)],
        'tipo_telefone' => tipos_contatos()[0],
        'opcional_celular' => opcoes_celular()[1],
        'contabil_id' => Contabil::count() > 0 ? Contabil::count() : factory('App\Contabil')->states('sem_login', 'low'),
    ];
});

$factory->state(PreRegistro::class, 'pj', function (Faker $faker) {
    $count = UserExterno::count();
    return [
        'user_externo_id' => ($count > 0) && !UserExterno::all()->get($count - 1)->isPessoaFisica() ? $count : factory('App\UserExterno')->states('pj'),
    ];
});

$factory->state(PreRegistro::class, 'sendo_elaborado', function (Faker $faker) {
    return [
        'idusuario' => null,
    ];
});

$factory->state(PreRegistro::class, 'analise_inicial', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_ANALISE_INICIAL,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
        'idusuario' => null,
    ];
});

$factory->state(PreRegistro::class, 'enviado_correcao', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_CORRECAO,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
        'historico_justificativas' => json_encode([
            json_encode([
                'segmento' => $faker->text(100), 'idregional' => $faker->text(100), 'cep' => $faker->text(100)
            ], JSON_FORCE_OBJECT) . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
        ], JSON_FORCE_OBJECT),
    ];
});

$factory->state(PreRegistro::class, 'analise_correcao', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_ANALISE_CORRECAO,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_CORRECAO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
        'historico_justificativas' => json_encode([
            json_encode([
                'segmento' => $faker->text(100), 'idregional' => $faker->text(100), 'cep' => $faker->text(100)
            ], JSON_FORCE_OBJECT) . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
        ], JSON_FORCE_OBJECT),
    ];
});

$factory->state(PreRegistro::class, 'aprovado', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_APROVADO,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->subDays(3)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_CORRECAO . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_APROVADO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
    ];
});

$factory->state(PreRegistro::class, 'negado', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_NEGADO,
        'justificativa' => json_encode(['negado' => $faker->sentence()], JSON_FORCE_OBJECT),
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->subDays(3)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_CORRECAO . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_NEGADO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
    ];
});

$factory->state(PreRegistro::class, 'aprovado_varias_justificativas', function (Faker $faker) {
    return [
        'status' => PreRegistro::STATUS_APROVADO,
        'historico_status' => json_encode([
            PreRegistro::STATUS_CRIADO . ';' . now()->subDays(3)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_INICIAL . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_CORRECAO . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_CORRECAO . ';' . now()->subDay()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_ANALISE_CORRECAO . ';' . now()->format('Y-m-d H:i:s'),
            PreRegistro::STATUS_APROVADO . ';' . now()->format('Y-m-d H:i:s')
        ], JSON_FORCE_OBJECT),
        'historico_justificativas' => json_encode([
            json_encode([
                'segmento' => $faker->text(100), 'idregional' => $faker->text(100), 'cep' => $faker->text(100)
            ], JSON_FORCE_OBJECT) . ';' . now()->subDays(2)->format('Y-m-d H:i:s'),
            json_encode([
                'uf' => $faker->text(100), 'path' => $faker->text(100)
            ], JSON_FORCE_OBJECT) . ';' . now()->subDay()->format('Y-m-d H:i:s'),
        ], JSON_FORCE_OBJECT),
    ];
});

$factory->state(PreRegistro::class, 'campos_ajax', function (Faker $faker) {
    return [
        'contabil_id' => null,
        'tipo_telefone_1' => tipos_contatos()[0],
        'telefone_1' => '(11) 99999-8888',
        'opcional_celular_1[]' => opcoes_celular()[1],
    ];
});

$factory->afterMakingState(PreRegistro::class, 'campos_ajax', function ($pr, $faker) {
    $pr->makeHidden([
        'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
        'historico_status', 'campos_espelho', 'campos_editados', 'historico_justificativas'
    ]);
});

$factory->afterCreatingState(PreRegistro::class, 'sendo_elaborado', function ($pr, $faker) {
    $pr->makeHidden([
        'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
        'historico_status', 'campos_espelho', 'campos_editados', 'historico_justificativas', 'created_at', 'updated_at', 'id'
    ]);
});
