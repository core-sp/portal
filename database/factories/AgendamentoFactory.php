<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Agendamento;
use Faker\Generator as Faker;

$factory->define(Agendamento::class, function (Faker $faker) {
    return [
        'nome' => 'Representante',
        'cpf' => '100.449.380-04',
        'email' => $faker->email,
        'celular' => '(11) 98765-4321',
        'tiposervico' => 'Teste',
        'idregional' => null,
        'protocolo' => null,
        'dia' => null,
        'hora' => null
    ];
});
