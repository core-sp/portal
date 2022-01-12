<?php

use App\Chamado;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Chamado::class, function (Faker $faker) {
    return [
        'tipo' => $faker->word,
        'prioridade' => 'Normal',
        'mensagem' => $faker->text($maxNbChars = 100),
        'img' => '/imagens/2022-01-12/teste.png',
        'resposta' => $faker->text($maxNbChars = 100),
        'idusuario' => factory('App\User'),
    ];
});
