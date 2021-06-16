<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\DadoFiscalizacao;
use Faker\Generator as Faker;

$factory->define(DadoFiscalizacao::class, function (Faker $faker) {
    return [
        "idregional" => 1,
        "idperiodo" => 1,
        "processofiscalizacaopf" => 1,
        "processofiscalizacaopj" => 2,
        "registroconvertidopf" => 3,
        "registroconvertidopj" => 4,
        "processoverificacao" => 5,
        "dispensaregistro" => 6,
        "notificacaort" => 7,
        "orientacaorepresentada" => 8,
        "orientacaorepresentante" => 9,
        "cooperacaoinstitucional" => 10
    ];
});
