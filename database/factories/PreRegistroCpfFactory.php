<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCpf;
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
        'titulo_eleitor' => '775692145782',
        'zona' => '123',
        'secao' => '12345',
        'ra_reservista' => '236587963257',
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

$factory->state(PreRegistroCpf::class, 'justificativas', function (Faker $faker) {
    return [];
});

$factory->afterCreatingState(PreRegistroCpf::class, 'justificativas', function ($prCpf, $faker) {
    $keys = array_merge(array_keys($prCpf->arrayValidacaoInputs()), array_keys($prCpf->preRegistro->arrayValidacaoInputs()));
    foreach($keys as $key)
        $array[$key] = $faker->text(100);
    $prCpf->preRegistro->update(['justificativa' => json_encode($array)]);
});
