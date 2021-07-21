<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Compromisso;
use Faker\Generator as Faker;

$factory->define(Compromisso::class, function (Faker $faker) {
    return [
        'titulo' => 'Compromisso',
        'descricao' => str_random(200),
        'local' => 'local do compromisso',
        'data' => date('Y-m-d'),
        'horarioinicio' => '12:00',
        'horariotermino' => '13:00'
    ];
});
