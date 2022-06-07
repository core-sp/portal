<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PreRegistroCpf;
use Faker\Generator as Faker;

$factory->define(PreRegistroCpf::class, function (Faker $faker) {
    return [
        'nome_social' => null,
        'dt_nascimento' => '1988-05-01',
        'sexo' => 'M',
        'estado_civil' => estados_civis()[0],
        'naturalidade' => estados()['SP'],
        'nacionalidade' => nacionalidades()[18],
        'nome_mae' => $faker->name,
        'nome_pai' => null,
        'identidade' => '111111111',
        'orgao_emissor' => 'SSP - SP',
        'dt_expedicao' => '2020-01-05',
        'pre_registro_id' => factory('App\PreRegistro'),
    ];
});
