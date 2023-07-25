<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCpf;
use App\PreRegistro;
use App\Contabil;
use Faker\Generator as Faker;

$factory->define(PreRegistroCpf::class, function (Faker $faker) {
    return [
        'nome_social' => mb_strtoupper($faker->name, 'UTF-8'),
        'dt_nascimento' => '1988-05-01',
        'sexo' => 'M',
        'estado_civil' => mb_strtoupper(estados_civis()[0], 'UTF-8'),
        'nacionalidade' => 'BRASILEIRA',
        'naturalidade_cidade' => mb_strtoupper('São Paulo', 'UTF-8'),
        'naturalidade_estado' => 'SP',
        'nome_mae' => mb_strtoupper($faker->name, 'UTF-8'),
        'nome_pai' => mb_strtoupper($faker->name, 'UTF-8'),
        'tipo_identidade' => mb_strtoupper(tipos_identidade()[0], 'UTF-8'),
        'identidade' => '111111111',
        'orgao_emissor' => 'SSP - SP',
        'dt_expedicao' => '2020-01-05',
        'pre_registro_id' => factory('App\PreRegistro'),
    ];
});

$factory->state(PreRegistroCpf::class, 'low', function (Faker $faker) {
    return [
        'nome_social' => $faker->name,
        'estado_civil' => estados_civis()[0],
        'nacionalidade' => nacionalidades()[17],
        'naturalidade_cidade' => 'São Paulo',
        'nome_mae' => $faker->name,
        'nome_pai' => $faker->name,
        'tipo_identidade' => tipos_identidade()[0],
        'pre_registro_id' => factory('App\PreRegistro')->states('low'),
    ];
});

$factory->state(PreRegistroCpf::class, 'justificado', function (Faker $faker) {
    $campos = ['segmento','cep','logradouro','numero','complemento','bairro','cidade','uf','telefone','telefone_1','tipo_telefone',
    'tipo_telefone_1','opcional_celular','opcional_celular_1','idregional','path','cnpj_contabil','nome_contabil','email_contabil','nome_contato_contabil',
    'telefone_contabil','nome_social','dt_nascimento','sexo','estado_civil','nacionalidade','naturalidade_cidade','naturalidade_estado','nome_mae','nome_pai','tipo_identidade','identidade',
    'orgao_emissor','dt_expedicao'];
    $arrayFinal = array();
    foreach($campos as $campo)
        $arrayFinal[$campo] = $faker->text(500);

    return [
        'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create([
            'justificativa' => json_encode($arrayFinal, JSON_FORCE_OBJECT)
        ]),
    ];
});

$factory->state(PreRegistroCpf::class, 'campos_editados', function (Faker $faker) {
    $campos = ['segmento','cep','logradouro','numero','complemento','bairro','cidade','uf','telefone','telefone_1','tipo_telefone',
    'tipo_telefone_1','opcional_celular','opcional_celular_1','idregional','cnpj_contabil','nome_contabil','email_contabil','nome_contato_contabil',
    'telefone_contabil','nome_social','dt_nascimento','sexo','estado_civil','nacionalidade','naturalidade_cidade','naturalidade_estado','nome_mae',
    'nome_pai','tipo_identidade','identidade','orgao_emissor','dt_expedicao'];
    $arrayFinal = array();
    foreach($campos as $campo)
        $arrayFinal[$campo] = null;

    return [
        'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create([
            'campos_editados' => json_encode($arrayFinal, JSON_FORCE_OBJECT)
        ]),
    ];
});

$factory->state(PreRegistroCpf::class, 'request', function (Faker $faker) {
    factory('App\Anexo')->states('pre_registro')->create();
    
    return [
        'pre_registro_id' => factory('App\PreRegistro')->states('low')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '13809835765'
            ])
        ]),
        'nome_social' => $faker->name,
        'estado_civil' => estados_civis()[0],
        'nacionalidade' => nacionalidades()[17],
        'naturalidade_cidade' => 'São Paulo',
        'nome_mae' => $faker->name,
        'nome_pai' => $faker->name,
        'tipo_identidade' => tipos_identidade()[0],
        'final' => null
    ];
});

$factory->afterMakingState(PreRegistroCpf::class, 'request', function ($prCpf, $faker) {
    $prCpf->preRegistro->opcional_celular = [opcoes_celular()[0], opcoes_celular()[2]];

    $contabil = array();
    foreach($prCpf->preRegistro->contabil->attributesToArray() as $key => $val)
        in_array($key, ['cnpj', 'nome', 'email', 'nome_contato', 'telefone']) ? $contabil[$key . '_contabil'] = $val : null;

    $pf = $prCpf->makeHidden(['id', 'pre_registro_id']);

    $pr = $prCpf->preRegistro->makeHidden(['id', 'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos',
    'historico_contabil', 'historico_status', 'historico_justificativas', 'campos_espelho', 'campos_editados', 'contabil', 'created_at', 'updated_at']);
    
    $prCpf->final = array_merge($pf->attributesToArray(), $pr->attributesToArray(), $contabil, ['pergunta' => 'Teste']);
});

$factory->afterCreatingState(PreRegistroCpf::class, 'justificado', function ($prCpf, $faker) {
    factory('App\Anexo')->states('pre_registro')->create();
});