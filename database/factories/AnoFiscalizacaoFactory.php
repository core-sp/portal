<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\AnoFiscalizacao;
use Faker\Generator as Faker;

$factory->define(AnoFiscalizacao::class, function (Faker $faker) {
    return [
        'ano' => '2020',
        'status' => '0'
    ];
});
