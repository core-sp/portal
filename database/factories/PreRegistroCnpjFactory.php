<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCnpj;
use App\ResponsavelTecnico;
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
        'numero' => (string) $faker->numberBetween(1, 5000) . 'A',
        'complemento' => null,
        'bairro' => 'TESTE BAIRRO',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'historico_rt' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
        'responsavel_tecnico_id' => ResponsavelTecnico::count() > 0 ? ResponsavelTecnico::count() : factory('App\ResponsavelTecnico'),
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
        'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'enviado_correcao')->create([
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
    factory('App\Anexo')->states('pre_registro')->create();

    return [
        'pre_registro_id' => factory('App\PreRegistro')->states('low')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '12434268000110'
            ])
        ]),
        'razao_social' => $faker->company,
        'tipo_empresa' => tipos_empresa()[0],
        'responsavel_tecnico_id' => ResponsavelTecnico::count() > 0 ? ResponsavelTecnico::count() : factory('App\ResponsavelTecnico')->states('low'),
        'final' => null
    ];
});

$factory->state(PreRegistroCnpj::class, 'request_mesmo_endereco', function (Faker $faker) {
    factory('App\Anexo')->states('pre_registro')->create();

    return [
        'pre_registro_id' => factory('App\PreRegistro')->states('low')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '12434268000110'
            ])
        ]),
        'razao_social' => $faker->company,
        'tipo_empresa' => tipos_empresa()[0],
        'cep' => null,
        'logradouro' => null,
        'numero' => null,
        'complemento' => null,
        'bairro' => null,
        'cidade' => null,
        'uf' => null,
        'responsavel_tecnico_id' => ResponsavelTecnico::count() > 0 ? ResponsavelTecnico::count() : factory('App\ResponsavelTecnico')->states('low'),
        'final' => null
    ];
});

$factory->afterMakingState(PreRegistroCnpj::class, 'request', function ($prCnpj, $faker) {
    $prCnpj->preRegistro->opcional_celular = [opcoes_celular()[0], opcoes_celular()[2]];

    $contabil = array();
    if(isset($prCnpj->preRegistro->contabil))
        foreach($prCnpj->preRegistro->contabil->attributesToArray() as $key => $val)
            in_array($key, ['cnpj', 'nome', 'email', 'nome_contato', 'telefone']) ? $contabil[$key . '_contabil'] = $val : null;

    $rt = array();
    if(isset($prCnpj->responsavelTecnico))
        foreach($prCnpj->responsavelTecnico->attributesToArray() as $key1 => $val1)
            !in_array($key1, ['registro', 'created_at', 'updated_at', 'deleted_at', 'id']) ? $rt[$key1 . '_rt'] = $val1 : null;

    $endereco = ['cep_empresa' => '01234-050', 'logradouro_empresa' => 'RUA TESTE DA RUA', 'numero_empresa' => '25A', 'complemento_empresa' => null, 
    'bairro_empresa' => 'TESTE BAIRRO', 'cidade_empresa' => 'SÃO PAULO', 'uf_empresa' => 'SP'];

    $pj = $prCnpj->makeHidden(['id', 'pre_registro_id', 'historico_rt', 'responsavel_tecnico_id']);

    $pr = $prCnpj->preRegistro->makeHidden(['id', 'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos',
    'historico_contabil', 'historico_status', 'historico_justificativas', 'campos_espelho', 'campos_editados', 'contabil', 'created_at', 'updated_at']);

    $prCnpj->final = array_merge($pj->attributesToArray(), $pr->attributesToArray(), $contabil, $rt, $endereco, ['pergunta' => 'Teste']);
});

$factory->afterMakingState(PreRegistroCnpj::class, 'request_mesmo_endereco', function ($prCnpj, $faker) {
    $prCnpj->preRegistro->opcional_celular = [opcoes_celular()[0], opcoes_celular()[2]];

    $contabil = array();
    if(isset($prCnpj->preRegistro->contabil))
        foreach($prCnpj->preRegistro->contabil->attributesToArray() as $key => $val)
            in_array($key, ['cnpj', 'nome', 'email', 'nome_contato', 'telefone']) ? $contabil[$key . '_contabil'] = $val : null;

    $rt = array();
    if(isset($prCnpj->responsavelTecnico))
        foreach($prCnpj->responsavelTecnico->attributesToArray() as $key1 => $val1)
            !in_array($key1, ['registro', 'created_at', 'updated_at', 'deleted_at', 'id']) ? $rt[$key1 . '_rt'] = $val1 : null;

    $pj = $prCnpj->makeHidden(['id', 'pre_registro_id', 'historico_rt', 'responsavel_tecnico_id']);

    $pr = $prCnpj->preRegistro->makeHidden(['id', 'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos',
    'historico_contabil', 'historico_status', 'historico_justificativas', 'campos_espelho', 'campos_editados', 'contabil', 'created_at', 'updated_at']);

    $endereco = ['cep' => $pr->cep, 'logradouro' => $pr->logradouro, 'numero' => $pr->numero, 'complemento' => $pr->complemento, 
    'bairro' => $pr->bairro, 'cidade' => $pr->cidade, 'uf' => $pr->uf];
    foreach($endereco as $key1 => $val1)
        $prCnpj->setAttribute($key1, $val1);

    $prCnpj->final = array_merge($pj->attributesToArray(), $pr->attributesToArray(), $contabil, $rt, ['checkEndEmpresa' => 'on', 'pergunta' => 'Teste']);
});

$factory->afterCreatingState(PreRegistroCnpj::class, 'justificado', function ($prCnpj, $faker) {
    factory('App\Anexo')->states('pre_registro')->create();
});

$factory->afterMaking(PreRegistroCnpj::class, function ($prCnpj, $faker) {
    $prCnpj->makeHidden(['pre_registro_id', 'historico_rt', 'responsavel_tecnico_id']);
});
