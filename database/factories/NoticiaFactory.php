<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Noticia;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Auth;

$factory->define(Noticia::class, function (Faker $faker) {
    return [
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User'),
        'titulo' => $titulo = $faker->sentence,
        'slug' => str_slug($titulo, '-'),
        'conteudo' => $faker->text
    ];
});
