<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\SalaReuniao;
use Faker\Generator as Faker;

$factory->define(SalaReuniao::class, function (Faker $faker) {
    $itensReuniao = SalaReuniao::itens();
    $itensReuniao['tv'] = str_replace('_', '32', $itensReuniao['tv']);
    $itensReuniao['cabo'] = str_replace('_', '1,5', $itensReuniao['cabo']);
    $itensReuniao['mesa'] = str_replace('_', '8', $itensReuniao['mesa']);

    return [
        'idregional' => factory('App\Regional'),
        'horarios_reuniao' => '09:00,10:00,11:00,14:00',
        'horarios_coworking' => '09:00,10:00,11:00,13:00',
        'participantes_reuniao' => 3,
        'participantes_coworking' => 2,
        'itens_reuniao' => json_encode(array_values($itensReuniao), JSON_FORCE_OBJECT),
        'itens_coworking' => json_encode(array_values(SalaReuniao::itensCoworking()), JSON_FORCE_OBJECT),
        'hora_limite_final_manha' => '12:00',
        'hora_limite_final_tarde' => '17:00',
        'idusuario' => auth()->guard('web')->id() !== null ? auth()->guard('web')->id() : factory('App\User')
    ];
});

$factory->state(SalaReuniao::class, 'desativa_reuniao', function ($faker) {
    return [
        'participantes_reuniao' => 0,
    ];
});

$factory->state(SalaReuniao::class, 'desativa_coworking', function ($faker) {
    return [
        'participantes_coworking' => 0,
    ];
});

$factory->state(SalaReuniao::class, 'desativa_ambos', function ($faker) {
    return [
        'participantes_reuniao' => 0,
        'participantes_coworking' => 0,
    ];
});
