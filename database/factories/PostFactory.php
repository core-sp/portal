<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User'),
        'titulo' => $titulo = $faker->sentence,
        'slug' => str_slug($titulo, '-'),
        'subtitulo' => $faker->sentence,
        'img' => $faker->url,
        'conteudo' => $faker->text,
        'created_at' => now(),
        'updated_at' => now()
    ];
});
