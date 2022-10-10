<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\DadoFiscalizacao;
use Faker\Generator as Faker;

$factory->define(DadoFiscalizacao::class, function (Faker $faker) {
    return [
        "idregional" => factory('App\Regional'),
        "idperiodo" => factory('App\PeriodoFiscalizacao'),
        "processofiscalizacaopf" => $faker->numberBetween(0, 999999999),
        "processofiscalizacaopj" => $faker->numberBetween(0, 999999999),
        "registroconvertidopf" => $faker->numberBetween(0, 999999999),
        "registroconvertidopj" => $faker->numberBetween(0, 999999999),
        "processoverificacao" => $faker->numberBetween(0, 999999999),
        "dispensaregistro" => $faker->numberBetween(0, 999999999),
        "notificacaort" => $faker->numberBetween(0, 999999999),
        "orientacaorepresentada" => $faker->numberBetween(0, 999999999),
        "orientacaorepresentante" => $faker->numberBetween(0, 999999999),
        "cooperacaoinstitucional" => $faker->numberBetween(0, 999999999),
        "autoconstatacao" => $faker->numberBetween(0, 999999999),
        "autosdeinfracao" => $faker->numberBetween(0, 999999999),
        "multaadministrativa" => $faker->numberBetween(0, 999999999),
        "orientacaocontabil" => $faker->numberBetween(0, 999999999),
        "oficioprefeitura" => $faker->numberBetween(0, 999999999),
        "oficioincentivo" => $faker->numberBetween(0, 999999999),
    ];
});

$factory->state(DadoFiscalizacao::class, 'raw_request', function ($faker) {
    $cont = 0;
    $final = array();
    $dados = [
        'id' => 1,
        'campo' => [
            "processofiscalizacaopf",
            "processofiscalizacaopj",
            "registroconvertidopf",
            "registroconvertidopj",
            "processoverificacao",
            "dispensaregistro",
            "notificacaort",
            "orientacaorepresentada",
            "orientacaorepresentante",
            "cooperacaoinstitucional",
            "autoconstatacao",
            "autosdeinfracao",
            "multaadministrativa",
            "orientacaocontabil",
            "oficioprefeitura",
            "oficioincentivo",
        ],
        'valor' => [
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
            $faker->numberBetween(0, 999999999),
        ],
    ];
    for($i = 0; $i < 13; $i++)
    {
        $dados['id'] = $i + 1;
        $final[$i] = $dados;
    }

    return [
        'final' => $final
    ];
});