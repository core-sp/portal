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

$factory->state(GerarTexto::class, 'carta-servicos', [
    'tipo_doc' => GerarTexto::DOC_CARTA_SERV,
]);

$factory->state(GerarTexto::class, 'prestacao-contas', [
    'tipo_doc' => GerarTexto::DOC_PREST_CONT,
    'conteudo' => null,
]);

$factory->state(GerarTexto::class, 'sumario_publicado', [
    'indice' => '1',
    'publicar' => 1,
]);

$factory->afterCreatingState(GerarTexto::class, 'sumario_publicado', function ($texto, $faker) {
    $id = $texto->id;
    $url = $faker->url;

    switch ($id) {
        case 2:
        case 7:
            $texto->update([
                'tipo' => GerarTexto::tipos()[1],
                'nivel' => 1,
                'indice' => '1.1',
                'ordem' => 2
            ]);
            break;
        case 3:
        case 8:
            $texto->update([
                'tipo' => GerarTexto::tipos()[1],
                'nivel' => 2,
                'indice' => '1.1.1',
                'ordem' => 3
            ]);
            break;
        case 4:
        case 9:
            $texto->update([
                'tipo' => GerarTexto::tipos()[1],
                'nivel' => 3,
                'indice' => '1.1.1.1',
                'ordem' => 4
            ]);
            if($texto->tipo_doc == 'prestacao-contas')
                $texto->update(['conteudo' => '<p><a href="'.$url.'">'.$url.'</a></p>']);
            break;
        case 5:
        case 10:
            $texto->update([
                'com_numeracao' => 0,
                'ordem' => 5,
                'indice' => null,
            ]);
            break;
        default:
            # code...
            break;
    }
});