<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\PeriodoFiscalizacao;
use Faker\Generator as Faker;

$factory->define(PeriodoFiscalizacao::class, function (Faker $faker) {
    return [
        'periodo' => '2020',
        'status' => '0'
    ];
});
