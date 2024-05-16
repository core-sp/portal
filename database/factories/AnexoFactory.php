<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Anexo;
use App\PreRegistro;
use Faker\Generator as Faker;

$factory->define(Anexo::class, function (Faker $faker) {
    return [
        'path' => null,
        'nome_original' => 'teste.jpg',
        'extensao' => 'jpg',
        'tamanho_bytes' => 28523,
        'tipo' => null,
        'pre_registro_id' => null,
    ];
});

$factory->state(Anexo::class, 'pre_registro', function (Faker $faker) {
    $id = PreRegistro::count() > 0 ? PreRegistro::count() : factory('App\PreRegistro')->create()->id;
    return [
        'path' => Anexo::PATH_PRE_REGISTRO . '/' . $id . '/' . (string) \Str::uuid() . '.jpg',
        'pre_registro_id' => $id
    ];
});

$factory->state(Anexo::class, 'zip', function (Faker $faker) {    
    return [
        'nome_original' => (string) \Str::uuid() . '.zip',
        'extensao' => 'zip',
    ];
});

$factory->afterCreatingState(Anexo::class, 'zip', function ($anexo, $faker) {
    $posicao = strrpos($anexo->path, '/') + 1;
    $path = substr_replace($anexo->path, $anexo->nome_original, $posicao, strlen($anexo->path));
    $anexo->update(['path' => $path]);
});