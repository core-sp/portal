<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Pagina;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Auth;

$factory->define(Pagina::class, function (Faker $faker) {
    return [
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User'),
        'titulo' => $titulo = $faker->sentence,
        'slug' => str_slug($titulo, '-'),
        'subtitulo' => $faker->sentence,
        'conteudo' => $faker->text
    ];
});
