<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Regional;
use Faker\Generator as Faker;

$factory->define(Regional::class, function (Faker $faker) {
    return [
        'prefixo' => 'SEDE',
        'regional' => 'São Paulo',
        'endereco' => 'Av. Brigadeiro Luís Antônio',
        'bairro' => 'Bela Vista',
        'numero' => '613',
        'complemento' => 'Térreo',
        'cep' => '01317-000',
        'telefone' => '(11) 3243-5500',
        'fax' => '(11) 3243-5520',
        'email' => 'corcesp@core-sp.org.br',
        'funcionamento' => $faker->sentence,
        'ageporhorario' => 1,
        'responsavel' => $faker->name,
        'descricao' => $faker->paragraph,
    ];
});
