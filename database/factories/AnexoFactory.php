<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Anexo;
use Faker\Generator as Faker;

$factory->define(Anexo::class, function (Faker $faker) {
    return [
        'path' => null,
        'nome_original' => 'teste.jpg',
        'pre_registro_id' => null,
    ];
});

$factory->state(Anexo::class, 'pre_registro', function (Faker $faker) {
    return [
        'path' => 'userExterno/pre_registros/' . (string) \Str::uuid(),
        'pre_registro_id' => factory('App\PreRegistro'),
    ];
});
