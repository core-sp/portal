<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\SalaReuniaoBloqueio;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(SalaReuniaoBloqueio::class, function (Faker $faker) {
    return [
        'dataInicial' => now()->addDay()->format('Y-m-d'),
        'dataFinal' => now()->addDays(7)->format('Y-m-d'),
        'horarios' => '10:00,11:00',
        'sala_reuniao_id' => factory('App\SalaReuniao'),
        'idusuario' => factory('App\User')
    ];
});
