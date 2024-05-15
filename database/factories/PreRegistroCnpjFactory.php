<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCnpj;
use App\ResponsavelTecnico;
use App\Socio;
use Faker\Generator as Faker;

$factory->define(PreRegistroCnpj::class, function (Faker $faker) {
    return [
        'razao_social' => mb_strtoupper($faker->company, 'UTF-8'),
        'nire' => 12345,
        'tipo_empresa' => mb_strtoupper(tipos_empresa()[0], 'UTF-8'),
        'dt_inicio_atividade' => '2020-12-21',
        'nome_fantasia' => mb_strtoupper($faker->company, 'UTF-8'),
        'capital_social' => '1.000,00',
        'cep' => '01234-050',
        'logradouro' => 'RUA TESTE DA RUA',
        'numero' => (string) $faker->numberBetween(1, 5000) . 'A',
        'complemento' => null,
        'bairro' => 'TESTE BAIRRO',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'historico_rt' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
        'historico_socio' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
        'responsavel_tecnico_id' => ResponsavelTecnico::count() > 0 ? ResponsavelTecnico::count() : factory('App\ResponsavelTecnico'),
        'pre_registro_id' => factory('App\PreRegistro')->states('pj'),
    ];
});

$factory->state(PreRegistroCnpj::class, 'low', function (Faker $faker) {
    return [
        'tipo_empresa' => tipos_empresa()[0],
        'razao_social' => $faker->company,
        'nome_fantasia' => $faker->company,
        'logradouro_empresa' => 'Rua Teste da rua',
        'numero_empresa' => (string) $faker->numberBetween(1, 5000) . 'a',
        'complemento_empresa' => null,
        'bairro_empresa' => 'Teste Bairro',
        'cidade_empresa' => 'São Paulo',
    ];
});

$factory->state(PreRegistroCnpj::class, 'make_endereco', function (Faker $faker) {
    return [
        'cep_empresa' => '01234-050',
        'logradouro_empresa' => 'RUA TESTE DA RUA',
        'numero_empresa' => (string) $faker->numberBetween(1, 5000) . 'A',
        'complemento_empresa' => null,
        'bairro_empresa' => 'TESTE BAIRRO',
        'cidade_empresa' => 'SÃO PAULO',
        'uf_empresa' => 'SP',
    ];
});

$factory->afterMakingState(PreRegistroCnpj::class, 'make_endereco', function ($prCnpj, $faker) {
    $prCnpj->makeHidden(['pre_registro_id', 'historico_rt', 'historico_socio', 'responsavel_tecnico_id', 'pre_registro']);
    $prCnpj->makeHidden(array_keys($prCnpj->getEndereco()));
});

$factory->afterMaking(PreRegistroCnpj::class, function ($prCnpj, $faker) {
    $prCnpj->makeHidden(['pre_registro_id', 'historico_rt', 'historico_socio', 'responsavel_tecnico_id', 'pre_registro']);
});

$factory->afterCreating(PreRegistroCnpj::class, function ($prCnpj, $faker) {    
    $socio_pf = factory('App\Socio')->create();
    $socio_pj = factory('App\Socio')->states('pj')->create();
    $prCnpj->socios()->attach($socio_pf->id, ['rt' => false]);
    $prCnpj->socios()->attach($socio_pj->id, ['rt' => false]);
});