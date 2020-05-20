<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Concurso;
use Faker\Generator as Faker;

$factory->define(Concurso::class, function (Faker $faker) {
    $date = new \DateTime();
    $date->add(new DateInterval('P30D'));
    $format = $date->format('Y-m-d\TH:i:s');
    
    $modalidades = concursoModalidades();
    $situacoes = concursoSituacoes();
    $modIndex = array_rand($modalidades);
    $sitIndex = array_rand($situacoes);

    return [
        'modalidade' => $modalidades[$modIndex],
        'situacao' => $situacoes[$sitIndex],
        'titulo' => $faker->sentence,
        'nrprocesso' => $faker->numberBetween(1000, 9999),
        'datarealizacao' => $format,
        'objeto' => str_random(200),
        'linkexterno' => $faker->url,
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User')
    ];
});
