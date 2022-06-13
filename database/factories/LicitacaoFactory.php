<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Licitacao;
use Faker\Generator as Faker;

$factory->define(Licitacao::class, function (Faker $faker) {
    return [
        'modalidade' => Licitacao::modalidadesLicitacao()[5],
        'situacao' => Licitacao::situacoesLicitacao()[3],
        'uasg' => '926753',
        'titulo' => $faker->sentence,
        'edital' => '/arquivos' . '/' . date('Y') . '-' . date('m') . '/teste.pdf',
        'nrlicitacao' => $faker->numberBetween(1, 999) . '/' . date('Y'),
        'nrprocesso' => $faker->numberBetween(1, 999) . '/' . date('Y'),
        'datarealizacao' => $faker->dateTime()->format('Y-m-d H:i:s'),
        'objeto' => $faker->text,
        'idusuario' => factory('App\User')
    ];
});
