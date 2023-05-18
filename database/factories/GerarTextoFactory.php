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

$factory->state(GerarTexto::class, 'sumario_publicado', [
    'indice' => '1',
    'publicar' => true,
]);

$factory->afterCreatingState(GerarTexto::class, 'sumario_publicado', function ($texto, $faker) {
    if($texto->id == 2)
        $texto->update([
            'tipo' => GerarTexto::tipos()[1],
            'nivel' => 1,
            'indice' => '1.1',
            'ordem' => 2
        ]);
    elseif($texto->id == 3)
        $texto->update([
            'tipo' => GerarTexto::tipos()[1],
            'nivel' => 2,
            'indice' => '1.1.1',
            'ordem' => 3
        ]);
    elseif($texto->id == 4)
        $texto->update([
            'tipo' => GerarTexto::tipos()[1],
            'nivel' => 3,
            'indice' => '1.1.1.1',
            'ordem' => 4
        ]);
    elseif($texto->id == 5)
        $texto->update([
            'com_numeracao' => false,
            'ordem' => 6,
            'indice' => null,
        ]);
});
