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
        'horarios_reuniao' => json_encode(['manha' => ['10:00','11:00'], 'tarde' => ['14:00', '15:00', '16:00']], JSON_FORCE_OBJECT),
        'horarios_coworking' => json_encode(['manha' => ['09:00','10:00'], 'tarde' => ['15:00', '16:00']], JSON_FORCE_OBJECT),
        'participantes_reuniao' => 4,
        'participantes_coworking' => 3,
        'itens_reuniao' => json_encode(array_values($itensReuniao), JSON_FORCE_OBJECT),
        'itens_coworking' => json_encode(array_values(SalaReuniao::itensCoworking()), JSON_FORCE_OBJECT),
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
