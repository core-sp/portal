<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Anexo;
use Faker\Generator as Faker;

$factory->define(Anexo::class, function (Faker $faker) {
    return [
        'path' => null,
        'nome_original' => 'teste.jpg',
        'extensao' => '.jpg',
        'tamanho_bytes' => 28523,
        'pre_registro_id' => null,
    ];
});
