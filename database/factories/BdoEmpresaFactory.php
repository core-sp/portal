<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\BdoEmpresa;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Auth;

$factory->define(BdoEmpresa::class, function (Faker $faker) {
    return [
        'segmento' => BdoEmpresa::segmentos()[$faker->numberBetween(0, 131)], 
        'cnpj' => '79.974.835/0001-00', 
        'razaosocial' => $faker->company, 
        'fantasia' => $faker->company,
        'descricao' => $faker->text, 
        'capitalsocial' => BdoEmpresa::capitalSocial()[$faker->numberBetween(0, 4)],
        'endereco' => $faker->streetName, 
        'site' => $faker->url, 
        'email' => $faker->email, 
        'telefone' => $faker->phoneNumber, 
        'contatonome' => $faker->name, 
        'contatotelefone' => $faker->phoneNumber, 
        'contatoemail' => $faker->email, 
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User')
    ];
});
