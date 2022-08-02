<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCpf;
use App\PreRegistro;
use Faker\Generator as Faker;

$factory->define(PreRegistroCpf::class, function (Faker $faker) {
    return [
        'nome_social' => null,
        'dt_nascimento' => '1988-05-01',
        'sexo' => 'M',
        'estado_civil' => mb_strtoupper(estados_civis()[0], 'UTF-8'),
        'nacionalidade' => 'BRASILEIRA',
        'naturalidade_cidade' => mb_strtoupper('São Paulo', 'UTF-8'),
        'naturalidade_estado' => mb_strtoupper('São Paulo', 'UTF-8'),
        'nome_mae' => mb_strtoupper($faker->name, 'UTF-8'),
        'nome_pai' => null,
        'tipo_identidade' => mb_strtoupper(tipos_identidade()[0], 'UTF-8'),
        'identidade' => '111111111',
        'orgao_emissor' => 'SSP - SP',
        'dt_expedicao' => '2020-01-05',
        'pre_registro_id' => factory('App\PreRegistro'),
    ];
});

$factory->state(PreRegistroCpf::class, 'low', function (Faker $faker) {
    return [
        'estado_civil' => estados_civis()[0],
        'nacionalidade' => nacionalidades()[18],
        'naturalidade_estado' => 'São Paulo',
        'nome_mae' => $faker->name,
        'tipo_identidade' => tipos_identidade()[0],
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

    $pr = factory('App\PreRegistro')->states('analise_inicial')->create([
        'justificativa' => json_encode($arrayFinal, JSON_FORCE_OBJECT)
    ]);

    return [
        'pre_registro_id' => $pr->id,
    ];
});
