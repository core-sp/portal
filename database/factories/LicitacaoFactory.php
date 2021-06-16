<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Licitacao;
use Faker\Generator as Faker;

$factory->define(Licitacao::class, function (Faker $faker) {
    $date = new \DateTime();
    $date->add(new DateInterval('P30D'));
    $format = $date->format('Y-m-d\TH:i:s');
    
    $modalidades = Licitacao::modalidadesLicitacao();
    $situacoes = Licitacao::situacoesLicitacao();
    $modIndex = array_rand($modalidades);
    $sitIndex = array_rand($situacoes);

    return [
        'modalidade' => $modalidades[$modIndex],
        'situacao' => $situacoes[$sitIndex],
        'uasg' => '926753',
        'titulo' => $faker->sentence,
        'nrlicitacao' => $faker->numberBetween(1000, 9999),
        'nrprocesso' => $faker->numberBetween(1000, 9999),
        'datarealizacao' => $format,
        'objeto' => $faker->text,
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User')
    ];
});
