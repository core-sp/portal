<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Aviso;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Auth;

$factory->define(Aviso::class, function (Faker $faker) {
    return [
        'area' => 'Representante',
        'titulo' => $faker->sentence,
        'conteudo' => $faker->text,
        'cor_fundo_titulo' => $faker->colorName,
        'status' => Aviso::DESATIVADO,
        'idusuario' => null
    ];
});
