<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\AgendamentoBloqueio;
use Faker\Generator as Faker;

$factory->define(AgendamentoBloqueio::class, function (Faker $faker) {
    return [
        'diainicio' => '2022-01-22',
        'diatermino' => '2022-01-28',
        'horainicio' => '10:00',
        'horatermino' => '11:00',
        'idregional' => factory('App\Regional'),
        'idusuario' => factory('App\User')
    ];
});
