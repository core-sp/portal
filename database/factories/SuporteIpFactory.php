<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\SuporteIp;
use Faker\Generator as Faker;

$factory->define(SuporteIp::class, function (Faker $faker) {
    return [
        'ip' => $faker->ipv4,
        'tentativas' => 1,
        'status' => SuporteIp::DESBLOQUEADO,
    ];
});

$factory->state(SuporteIp::class, 'liberado', [
    'status' => SuporteIp::LIBERADO,
    'tentativas' => 0,
]);

$factory->state(SuporteIp::class, 'bloqueado', [
    'tentativas' => SuporteIp::TOTAL_TENTATIVAS,
    'status' => SuporteIp::BLOQUEADO,
]);
