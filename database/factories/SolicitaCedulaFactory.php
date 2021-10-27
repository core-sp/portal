<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SolicitaCedula;
use Faker\Generator as Faker;

$factory->define(SolicitaCedula::class, function (Faker $faker) {
    return [
        'idregional' => factory('App\Regional'),
        'cep' => $faker->postcode,
        'bairro' => $faker->streetAddress,
        'logradouro' => $faker->streetName,
        'numero' => $faker->numberBetween(1, 10000),
        'complemento' => $faker->word,
        'estado' => $faker->cityPrefix,
        'municipio' => $faker->city,
        'status' => SolicitaCedula::STATUS_EM_ANDAMENTO,
        'idrepresentante' => factory('App\Representante'),
        'idusuario' => Auth::id() !== null ? Auth::id() : null
    ];
});
