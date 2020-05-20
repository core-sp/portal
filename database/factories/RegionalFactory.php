<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Regional;
use Faker\Generator as Faker;

$factory->define(Regional::class, function (Faker $faker) {
    return [
        'prefixo' => $faker->citySuffix,
        'regional' => $faker->city,
        'endereco' => $faker->streetName,
        'bairro' => $faker->streetAddress,
        'numero' => $faker->numberBetween(1, 10000),
        'complemento' => $faker->word,
        'cep' => $faker->postcode,
        'telefone' => $faker->phoneNumber,
        'fax' => $faker->phoneNumber,
        'email' => $faker->email,
        'funcionamento' => $faker->sentence,
        'ageporhorario' => $faker->numberBetween(1, 5),
        'responsavel' => $faker->name,
        'descricao' => $faker->paragraph,
    ];
});
