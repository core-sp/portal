<?php

use App\HomeImagem;
use Faker\Generator as Faker;

$factory->define(HomeImagem::class, function (Faker $faker) {
    return [
        'funcao' => 'bannerprincipal',
        'ordem' => 1,
        'url' => '/imagens/'.date('Y-m').'/'.$faker->word.'.png',
        'url_mobile' => '/imagens/'.date('Y-m').'/'.$faker->word.'.png',
        'link' => $faker->url,
        'target' => '_blank'
    ];
});
