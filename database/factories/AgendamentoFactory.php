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
        'tiposervico' => 'Atualização de Cadastro para PF',
        'idregional' => factory('App\Regional'),
        'protocolo' => 'AGE-XXXXXX',
        'dia' => date('Y-m-d', strtotime('+1 day')),
        'hora' => '10:00',
        'status' => null,
        'idusuario' => null
    ];
});
