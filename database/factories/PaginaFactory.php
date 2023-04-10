<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Pagina;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Pagina::class, function (Faker $faker) {
    $titulo = $faker->sentence;
    $conteudo = $faker->sentence(400);
    return [
        'titulo' => $titulo,
        'subtitulo' => $faker->sentence,
        'slug' => Str::slug($titulo, '-'),
        'img' => $faker->url,
        'conteudo' => $conteudo,
        'conteudoBusca' => converterParaTextoCru($conteudo),
        'idusuario' => factory('App\User'),
    ];
});
