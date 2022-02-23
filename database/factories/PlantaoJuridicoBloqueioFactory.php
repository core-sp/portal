<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\PlantaoJuridicoBloqueio;
use Faker\Generator as Faker;

$factory->define(PlantaoJuridicoBloqueio::class, function (Faker $faker) {
    $plantao = factory('App\PlantaoJuridico')->create();
    return [
        'idplantaojuridico' => $plantao->id,
        'dataInicial' => $plantao->dataInicial,
        'dataFinal' => $plantao->dataFinal,
        'horarios' => '10:00,11:00',
        'idusuario' => factory('App\User')
    ];
});
