<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCpf;
use Faker\Generator as Faker;

$factory->define(PreRegistroCpf::class, function (Faker $faker) {
    return [
        'nome_social' => null,
        'dt_nascimento' => '1988-05-01',
        'sexo' => 'M',
        'estado_civil' => mb_strtoupper(estados_civis()[0], 'UTF-8'),
        'naturalidade' => mb_strtoupper('São Paulo', 'UTF-8'),
        'nacionalidade' => mb_strtoupper(nacionalidades()[18], 'UTF-8'),
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
        'naturalidade' => 'São Paulo',
        'nome_mae' => $faker->name,
        'tipo_identidade' => tipos_identidade()[0],
    ];
});
