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
        'status' => Aviso::DESATIVADO,
        'idusuario' => auth()->id() !== null ? auth()->id() : factory('App\User')
    ];
});

$factory->state(Aviso::class, 'bdo', [
    'area' => Aviso::areas()[1],
    'titulo' => '-----------',
]);
