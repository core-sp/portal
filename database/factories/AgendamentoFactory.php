<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Agendamento;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(Agendamento::class, function (Faker $faker) {
    $amanha = Carbon::tomorrow();

    while($amanha->isWeekend())
        $amanha->addDay();

    return [
        'nome' => 'Representante',
        'cpf' => '100.449.380-04',
        'email' => $faker->email,
        'celular' => '(11) 98765-4321',
        'tiposervico' => Agendamento::SERVICOS_ATUALIZACAO_DE_CADASTRO.' para PF',
        'idregional' => factory('App\Regional'),
        'protocolo' => 'AGE-XXXXXX',
        'dia' => $amanha->format('Y-m-d'),
        'hora' => '10:00',
        'status' => null,
        'idusuario' => null
    ];
});
