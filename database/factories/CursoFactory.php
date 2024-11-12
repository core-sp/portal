<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Curso;
use Faker\Generator as Faker;

$factory->define(Curso::class, function (Faker $faker) {
    return [
        'tipo' => Curso::tipos()[0],
        'tema' => $faker->sentence,
        'conferencista' => $faker->name,
        'carga_horaria' => '00:00',
        'img' => '/imagens/2019-05/notícias-genérico.jpg',
        'datarealizacao' => now()->addDays(2)->format('Y-m-d H:i'),
        'datatermino' => now()->addDays(2)->addHours(2)->format('Y-m-d H:i'),
        'inicio_inscricao' => now()->format('Y-m-d H:i'),
        'termino_inscricao' => now()->addDay()->addHour()->format('Y-m-d H:i'),
        'endereco' => $faker->address,
        'nrvagas' => $faker->numberBetween(10, 9999),
        'descricao' => $faker->text,
        'resumo' => $faker->sentence,
        'acesso' => Curso::ACESSO_PRI,
        'publicado' => 'Sim',
        'add_campo' => 0,
        'campo_rotulo' => null,
        'campo_required' => 0,
        'idregional' => factory('App\Regional'),
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User')
    ];
});

$factory->state(Curso::class, 'publico', function (Faker $faker) {
    return [
        'acesso' => Curso::ACESSO_PUB,
    ];
});

$factory->state(Curso::class, 'campo_adicional_required', function (Faker $faker) {
    return [
        'add_campo' => 1,
        'campo_rotulo' => array_keys(Curso::rotulos())[0],
        'campo_required' => 1,
    ];
});

$factory->state(Curso::class, 'campo_adicional', function (Faker $faker) {
    return [
        'add_campo' => 1,
        'campo_rotulo' => array_keys(Curso::rotulos())[0],
    ];
});
