<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\PlantaoJuridico;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(PlantaoJuridico::class, function (Faker $faker) {
    $amanha = Carbon::tomorrow();
    $depois_amanha = Carbon::tomorrow()->addDays(2);

    while($amanha->isWeekend() || $depois_amanha->isWeekend())
    {
        $amanha->addDay();
        $depois_amanha->addDay();
    }

    return [
        'idregional' => factory('App\Regional'),
        'qtd_advogados' => 0,
        'horarios' => '10:00,10:30,11:00,11:30,12:00,12:30',
        'dataInicial' => $amanha->format('Y-m-d'),
        'dataFinal' => $depois_amanha->format('Y-m-d'),
    ];
});
