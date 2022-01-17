<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\RepresentanteEndereco;
use Faker\Generator as Faker;

$factory->define(RepresentanteEndereco::class, function (Faker $faker) {
    $rep = factory('App\Representante')->create();
    return [
        'ass_id' => $rep->ass_id,
        'cep' => '04445-445',
        'bairro' => 'Teste do Bairro',
        'logradouro' => 'Teste do Logradouro',
        'numero' => 55,
        'complemento' => null,
        'estado' => 'SP',
        'municipio' => 'São Paulo',
        'crimage' => $rep->id.'-'.time().'jpg',
        'crimagedois' => null,
        'status' => 'Aguardando confirmação',
        'observacao' => null
    ];
});
