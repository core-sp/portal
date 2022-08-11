<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCnpj;
use App\PreRegistro;
use App\ResponsavelTecnico;
use App\Contabil;
use Faker\Generator as Faker;

$factory->define(PreRegistroCnpj::class, function (Faker $faker) {
    return [
        'razao_social' => mb_strtoupper($faker->company, 'UTF-8'),
        'nire' => 12345,
        'tipo_empresa' => mb_strtoupper(tipos_empresa()[0], 'UTF-8'),
        'dt_inicio_atividade' => '2020-12-21',
        'inscricao_municipal' => 123456,
        'inscricao_estadual' => 123456789,
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

    return [
        'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create([
            'justificativa' => json_encode($arrayFinal, JSON_FORCE_OBJECT)
        ]),
    ];
});

$factory->state(PreRegistroCnpj::class, 'campos_editados', function (Faker $faker) {
    $campos = ['segmento','cep','logradouro','numero','complemento','bairro','cidade','uf','telefone','telefone_1','tipo_telefone',
    'tipo_telefone_1','opcional_celular','opcional_celular_1','idregional','cnpj_contabil','nome_contabil','email_contabil','nome_contato_contabil',
    'telefone_contabil','cpf_rt','nome_rt','nome_social_rt','sexo_rt','dt_nascimento_rt','cep_rt','logradouro_rt','numero_rt','complemento_rt',
    'bairro_rt','cidade_rt','uf_rt','nome_mae_rt','nome_pai_rt','tipo_identidade_rt','identidade_rt','orgao_emissor_rt','dt_expedicao_rt','razao_social',
    'nire','tipo_empresa','dt_inicio_atividade','inscricao_municipal','inscricao_estadual','capital_social','cep_empresa','logradouro_empresa','numero_empresa',
    'complemento_empresa','bairro_empresa','cidade_empresa','uf_empresa'];
    $arrayFinal = array();
    foreach($campos as $campo)
        $arrayFinal[$campo] = null;

    return [
        'pre_registro_id' => factory('App\PreRegistro')->states('pj','analise_correcao')->create([
            'campos_editados' => json_encode($arrayFinal, JSON_FORCE_OBJECT)
        ]),
    ];
});

$factory->state(PreRegistroCnpj::class, 'request', function (Faker $faker) {
    $contabil = factory('App\Contabil')->states('low')->make([
        'cnpj' => '35195123000100'
    ])->attributesToArray();
    foreach($contabil as $key => $val)
        $contabil['final'][$key . '_contabil'] = $val;

    $rt = factory('App\ResponsavelTecnico')->states('low')->make([
        'cpf' => '42214340076'
    ])->makeHidden(['registro'])->attributesToArray();
    foreach($rt as $key => $val)
        $rt['final'][$key . '_rt'] = $val;

    $pr = factory('App\PreRegistro')->states('low','pj')->make([
        'contabil_id' => Contabil::count() + 1
    ])->attributesToArray();
    $pr['final'] = $pr;
    $pr['final']['opcional_celular'] = [opcoes_celular()[0], opcoes_celular()[2]];
    $pr['final']['tipo_telefone_1'] = tipos_contatos()[0];
    $pr['final']['telefone_1'] = '(11) 00000-0000';
    $pr['final']['opcional_celular_1'] = [opcoes_celular()[1], opcoes_celular()[0]];

    $endereco = [
        'cep_empresa' => '01234-050',
        'logradouro_empresa' => 'RUA TESTE DA RUA',
        'numero_empresa' => '25A',
        'complemento_empresa' => null,
        'bairro_empresa' => 'TESTE BAIRRO',
        'cidade_empresa' => 'SÃO PAULO',
        'uf_empresa' => 'SP',
    ];

    $final = array_merge($pr['final'], $contabil['final'], $rt['final'], $endereco);
    unset($pr['final']);
    unset($contabil['final']);
    unset($rt['final']);

    $pr['opcional_celular'] = opcoes_celular()[0] . ',' . opcoes_celular()[2] . ';' . opcoes_celular()[1] . ',' . opcoes_celular()[0];
    $pr['tipo_telefone'] = tipos_contatos()[0] . ';' . tipos_contatos()[0];
    $pr['telefone'] = '(11) 00000-0000;(11) 00000-0000';

    return [
        'razao_social' => $faker->company,
        'tipo_empresa' => tipos_empresa()[0],
        'pre_registro_id' => PreRegistro::count() + 1,
        'responsavel_tecnico_id' => ResponsavelTecnico::count() + 1,
        'preRegistro' => $pr,
        'contabil' => $contabil,
        'rt' => $rt,
        'final' => $final
    ];
});
