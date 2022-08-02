<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCnpj;
use Faker\Generator as Faker;

$factory->define(PreRegistroCnpj::class, function (Faker $faker) {
    return [
        'razao_social' => mb_strtoupper($faker->company, 'UTF-8'),
        'nire' => null,
        'tipo_empresa' => mb_strtoupper(tipos_empresa()[0], 'UTF-8'),
        'dt_inicio_atividade' => '2020-12-21',
        'inscricao_municipal' => null,
        'inscricao_estadual' => null,
        'capital_social' => '1.000,00',
        'cep' => '01234-050',
        'logradouro' => 'RUA TESTE DA RUA',
        'numero' => '25A',
        'complemento' => null,
        'bairro' => 'TESTE BAIRRO',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'historico_rt' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
        'responsavel_tecnico_id' => factory('App\ResponsavelTecnico'),
        'pre_registro_id' => factory('App\PreRegistro')->states('pj'),
    ];
});

$factory->state(PreRegistroCnpj::class, 'low', function (Faker $faker) {
    return [
        'razao_social' => $faker->company,
        'tipo_empresa' => tipos_empresa()[0],
    ];
});

$factory->state(PreRegistroCnpj::class, 'justificado', function (Faker $faker) {
    $campos = ['segmento','cep','logradouro','numero','complemento','bairro','cidade','uf','telefone','telefone_1','tipo_telefone',
    'tipo_telefone_1','opcional_celular','opcional_celular_1','idregional','path','cnpj_contabil','nome_contabil','email_contabil','nome_contato_contabil',
    'telefone_contabil','cpf_rt','registro','nome_rt','nome_social_rt','sexo_rt','dt_nascimento_rt','cep_rt','logradouro_rt','numero_rt','complemento_rt',
    'bairro_rt','cidade_rt','uf_rt','nome_mae_rt','nome_pai_rt','tipo_identidade_rt','identidade_rt','orgao_emissor_rt','dt_expedicao_rt','razao_social',
    'nire','tipo_empresa','dt_inicio_atividade','inscricao_municipal','inscricao_estadual','capital_social','cep_empresa','logradouro_empresa','numero_empresa',
    'complemento_empresa','bairro_empresa','cidade_empresa','uf_empresa'];
    $arrayFinal = array();
    foreach($campos as $campo)
        $arrayFinal[$campo] = $faker->text(500);

    $pr = factory('App\PreRegistro')->states('pj', 'analise_inicial')->create([
        'justificativa' => json_encode($arrayFinal, JSON_FORCE_OBJECT)
    ]);

    return [
        'pre_registro_id' => $pr->id,
    ];
});
