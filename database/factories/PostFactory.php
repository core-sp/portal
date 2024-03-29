<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Post;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Post::class, function (Faker $faker) {
    $titulo = $faker->sentence;
    $conteudo = $faker->text;
    return [
        'titulo' => $titulo,
        'slug' => Str::slug($titulo, '-'),
        'subtitulo' => $faker->sentence,
        'img' => $faker->url,
        'conteudo' => $conteudo,
        'conteudoBusca' => converterParaTextoCru($conteudo),
        'idusuario' => factory('App\User'),
    ];
});
