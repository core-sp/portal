<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Curso;
use Faker\Generator as Faker;

$factory->define(Curso::class, function (Faker $faker) {
    $tipos = cursoTipos();
    $tiposIndex = array_rand($tipos);

    return [
        'tipo' => $tipos[$tiposIndex],
        'tema' => $faker->sentence,
        'img' => '/imagens/2019-05/notícias-genérico.jpg',
        'datarealizacao' => now()->addDays(2)->format('Y-m-d H:i'),
        'datatermino' => now()->addDays(2)->addHours(2)->format('Y-m-d H:i'),
        'inicio_inscricao' => now()->format('Y-m-d H:i'),
        'termino_inscricao' => now()->addDay()->addHour()->format('Y-m-d H:i'),
        'endereco' => $faker->address,
        'nrvagas' => $faker->numberBetween(10, 200),
        'descricao' => $faker->text,
        'resumo' => $faker->sentence,
        'acesso' => Curso::ACESSO_PRI,
        'publicado' => 'Sim',
        'idregional' => factory('App\Regional'),
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User')
    ];
});

$factory->state(Curso::class, 'publico', function (Faker $faker) {
    return [
        'acesso' => Curso::ACESSO_PUB,
    ];
});
