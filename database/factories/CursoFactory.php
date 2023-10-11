<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Curso;
use Faker\Generator as Faker;

$factory->define(Curso::class, function (Faker $faker) {
    $date = new \DateTime();
    $date->add(new DateInterval('P30D'));
    $realizacao = $date->format('Y-m-d\TH:i:s');
    $new_date = new \DateTime();
    $new_date->add(new DateInterval('P31D'));
    $termino = $new_date->format('Y-m-d\TH:i:s');

    $tipos = cursoTipos();
    $tiposIndex = array_rand($tipos);

    return [
        'tipo' => $tipos[$tiposIndex],
        'tema' => $faker->sentence,
        'img' => '/imagens/2019-05/notícias-genérico.jpg',
        'datarealizacao' => $realizacao,
        'datatermino' => $termino,
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
