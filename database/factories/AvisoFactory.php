<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Aviso;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Auth;

$factory->define(Aviso::class, function (Faker $faker) {
    return [
        'area' => Aviso::areas()[0],
        'titulo' => $faker->sentence,
        'conteudo' => $faker->text,
        'cor_fundo_titulo' => Aviso::cores()[0],
        'dia_hora_ativar' => null,
        'dia_hora_desativar' => null,
        'status' => Aviso::DESATIVADO,
        'idusuario' => auth()->id() !== null ? auth()->id() : factory('App\User')
    ];
});

$factory->state(Aviso::class, 'bdo', [
    'area' => Aviso::areas()[1],
    'titulo' => '-----------',
]);

$factory->state(Aviso::class, 'anuidade', [
    'area' => Aviso::areas()[2],
    'titulo' => '-----------',
]);

$factory->state(Aviso::class, 'agendamento', [
    'area' => Aviso::areas()[3],
    'titulo' => '-----------',
]);

$factory->state(Aviso::class, 'data_desativar', [
    'dia_hora_desativar' => now()->format('Y-m-d H:i'),
    'status' => Aviso::ATIVADO,
]);

$factory->state(Aviso::class, 'data_ativar', [
    'dia_hora_ativar' => now()->format('Y-m-d H:i'),
    'status' => Aviso::DESATIVADO,
]);

$factory->state(Aviso::class, 'data_ativar_desativar', [
    'dia_hora_ativar' => now()->format('Y-m-d H:i'),
    'dia_hora_desativar' => now()->addDay()->format('Y-m-d H:i'),
    'status' => Aviso::DESATIVADO,
]);
