<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SolicitaCedula;
use Faker\Generator as Faker;

$factory->define(SolicitaCedula::class, function (Faker $faker) {
    return [
        'idregional' => factory('App\Regional'),
        'rg' => '123456789',
        'cpf' => '19700585018',
        'nome' => $faker->name,
        'cep' => '01112-000',
        'bairro' => $faker->streetAddress,
        'logradouro' => $faker->streetName,
        'numero' => $faker->numberBetween(1, 10000),
        'complemento' => $faker->word,
        'estado' => 'SP',
        'municipio' => $faker->city,
        'tipo' => SolicitaCedula::TIPO_AMBOS,
        'status' => SolicitaCedula::STATUS_EM_ANDAMENTO,
        'idrepresentante' => factory('App\Representante'),
        'idusuario' => null
    ];
});
