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
        'img' => '/imagens/fake/'.date('Y-m').'/desktop_'.$faker->word. $faker->ean8 . '.png',
        'subtitulo' => $faker->sentence,
        'conteudo' => $faker->text
    ];
});
