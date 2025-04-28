<?php

use App\Sessao;
use Faker\Generator as Faker;

$factory->define(Sessao::class, function (Faker $faker) {
    return [
        'idusuario' => factory('App\User'),
        'ip_address' => $faker->ipv4,
        'ultimo_acesso' => now()->subDay()->subHour()->subMinute()->format('Y-m-d h:i:s'),
    ];
});
