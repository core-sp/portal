<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\AgendamentoSala;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(AgendamentoSala::class, function (Faker $faker) {
    $amanha = Carbon::tomorrow();

    while($amanha->isWeekend())
        $amanha->addDay();

    return [
        'protocolo' => 'RC-AGE-XXXXXX12',
        'idrepresentante' => auth()->guard('representante')->id() !== null ? auth()->guard('representante')->id() : factory('App\Representante'),
        'participantes' => null,
        'dia' => $amanha->format('Y-m-d'),
        'periodo' => 'manha',
        'tipo_sala' => 'coworking',
        'status' => null,
        'justificativa' => null,
        'anexo' => null,
        'sala_reuniao_id' => factory('App\SalaReuniao'),
        'idusuario' => null
    ];
});

$factory->state(AgendamentoSala::class, 'reuniao', function ($faker) {
    return [
        'protocolo' => 'RC-AGE-XXXXXX13',
        'participantes' => json_encode(['56983238010' => 'NOME PARTICIPANTE UM', '81921923008' => 'NOME PARTICIPANTE DOIS'], JSON_FORCE_OBJECT),
        'tipo_sala' => 'reuniao',
    ];
});