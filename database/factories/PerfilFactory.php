<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Perfil;
use Faker\Generator as Faker;

$factory->define(Perfil::class, function (Faker $faker) {
    return [
        'nome' => $faker->name
    ];
});

$factory->state(Perfil::class, 'bloqueado', function ($faker) {
    return [
        'idperfil' => 24,
        'nome' => 'Bloqueado',
    ];
});
