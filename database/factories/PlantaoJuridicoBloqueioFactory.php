<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\PlantaoJuridicoBloqueio;
use Faker\Generator as Faker;

$factory->define(PlantaoJuridicoBloqueio::class, function (Faker $faker) {
    return [
        'idplantaojuridico' => factory('App\PlantaoJuridico'),
        'dataInicial' => date('Y-m-d'),
        'dataFinal' => date('Y-m-d'),
        'horarios' => '10:00,11:00',
        'idusuario' => factory('App\User')
    ];
});
