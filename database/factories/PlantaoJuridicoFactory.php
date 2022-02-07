<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\PlantaoJuridico;
use Faker\Generator as Faker;

$factory->define(PlantaoJuridico::class, function (Faker $faker) {
    return [
        'idregional' => factory('App\Regional'),
        'qtd_advogados' => 0,
        'horarios' => '10:00,11:00,11:30,12:00,12:30',
        'dataInicial' => date('Y-m-d'),
        'dataFinal' => date('Y-m-d')
    ];
});
