<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\GerarTexto;
use Faker\Generator as Faker;

$factory->define(GerarTexto::class, function (Faker $faker) {
    return [
        'tipo' => GerarTexto::tipos()[0],
        'texto_tipo' => $faker->sentence,
        'conteudo' => $faker->sentence(400),
        'com_numeracao' => 1,
        'ordem' => 1,
        'nivel' => 0,
        'tipo_doc' => array_keys(GerarTexto::tiposDoc())[0],
        'indice' => null,
        'publicar' => 0,
    ];
});

$factory->state(GerarTexto::class, 'sumario_publicado', [
    'indice' => '1',
    'publicar' => 1,
]);

$factory->afterCreatingState(GerarTexto::class, 'sumario_publicado', function ($texto, $faker) {
    $id = $texto->id;
    switch ($id) {
        case 2:
            $texto->update([
                'tipo' => GerarTexto::tipos()[1],
                'nivel' => 1,
                'indice' => '1.1',
                'ordem' => 2
            ]);
            break;
        case 3:
            $texto->update([
                'tipo' => GerarTexto::tipos()[1],
                'nivel' => 2,
                'indice' => '1.1.1',
                'ordem' => 3
            ]);
            break;
        case 4:
            $texto->update([
                'tipo' => GerarTexto::tipos()[1],
                'nivel' => 3,
                'indice' => '1.1.1.1',
                'ordem' => 4
            ]);
            break;
        case 5:
            $texto->update([
                'com_numeracao' => 0,
                'ordem' => 6,
                'indice' => null,
            ]);
            break;
        default:
            # code...
            break;
    }
});
