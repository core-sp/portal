<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\AgendamentoBloqueio;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(AgendamentoBloqueio::class, function (Faker $faker) {
    $amanha = Carbon::tomorrow();

    while($amanha->isWeekend())
        $amanha->addDay();

    return [
        'diainicio' => date('Y-m-d'),
        'diatermino' => $amanha->format('Y-m-d'),
        'horarios' => '10:00',
        'qtd_atendentes' => 0,
        'idregional' => factory('App\Regional'),
        'idusuario' => factory('App\User')
    ];
});
