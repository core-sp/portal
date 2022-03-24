<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\AgendamentoBloqueio;
use Faker\Generator as Faker;

$factory->define(AgendamentoBloqueio::class, function (Faker $faker) {
    $regional = factory('App\Regional')->create();
    return [
        'diainicio' => date('Y-m-d'),
        'diatermino' => date('Y-m-d'),
        'horarios' => '10:00',
        'qtd_atendentes' => $regional->ageporhorario,
        'idregional' => $regional->idregional,
        'idusuario' => factory('App\User')
    ];
});
