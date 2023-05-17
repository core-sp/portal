<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\GerarTexto;
use Faker\Generator as Faker;

$factory->define(GerarTexto::class, function (Faker $faker) {
    return [
        'tipo' => GerarTexto::tipos()[0],
        'texto_tipo' => $faker->sentence,
        'conteudo' => $faker->sentence(400),
        'com_numeracao' => true,
        'ordem' => 1,
        'nivel' => 0,
        'tipo_doc' => array_keys(GerarTexto::tipos_doc())[0],
        'indice' => null,
        'publicar' => false,
    ];
});
