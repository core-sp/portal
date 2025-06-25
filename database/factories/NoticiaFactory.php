<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Noticia;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Noticia::class, function (Faker $faker) {
    $titulo = $faker->sentence;
    $conteudo = $faker->sentence(400);
    return [
        'titulo' => $titulo,
        'slug' => Str::slug($titulo, '-'),
        'img' => '/imagens/fake/'.date('Y-m').'/desktop_'.$faker->word. ' úç ãÌ()Ë ' .$faker->ean8 . '.png',
        'conteudo' => $conteudo,
        'conteudoBusca' => converterParaTextoCru($conteudo),
        'categoria' => null,
        'publicada' => 'Sim',
        'idregional' => factory('App\Regional'),
        'idcurso' => factory('App\Curso'),
        'idusuario' => factory('App\User'),
    ];
});
