<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\AgendamentoBloqueio;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(AgendamentoBloqueio::class, function (Faker $faker) {
    return [
        'diainicio' => date('Y-m-d'),
        'diatermino' => Carbon::tomorrow()->format('Y-m-d'),
        'horarios' => '10:00',
        'qtd_atendentes' => 0,
        'idregional' => factory('App\Regional'),
        'idusuario' => factory('App\User')
    ];
});
