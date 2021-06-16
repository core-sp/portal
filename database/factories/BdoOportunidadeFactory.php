<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\BdoEmpresa;
use App\BdoOportunidade;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Auth;

$factory->define(BdoOportunidade::class, function (Faker $faker) {
    return [
        'idempresa' => 1,
        'titulo' => $faker->sentence,
        'segmento' => BdoEmpresa::segmentos()[$faker->numberBetween(0, 131)], 
        'regiaoatuacao' => ',1,2,3,4,5,6,7,8,9,10,11,12,13,',
        'descricao' => $faker->text,
        'vagasdisponiveis' => $faker->numberBetween(1, 99),
        'vagaspreenchidas' => null,
        'status' => BdoOportunidade::STATUS_EM_ANDAMENTO,
        'observacao' => null,
        'datainicio' => now(),
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User')
    ];
});