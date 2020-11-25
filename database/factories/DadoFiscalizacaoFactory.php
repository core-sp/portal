<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\DadoFiscalizacao;
use Faker\Generator as Faker;

$factory->define(DadoFiscalizacao::class, function (Faker $faker) {
    return [
        "idregional" => 1,
        "ano" => 2020,
        "notificacaopf" => 1,
        "notificacaopj" => 2,
        "constatacaopf" => 3,
        "constatacaopj" => 4,
        "infracaopf" => 5,
        "infracaopj" => 6,
        "convertidopf" => 7,
        "convertidopj" => 8,
        "orientacao" => 9
    ];
});
