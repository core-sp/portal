<?php

use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

$factory->define(User::class, function (Faker $faker) {
    return [
        'nome' => 'Usuário',
        'email' => $faker->unique()->safeEmail,
        'username' => $faker->userName,
        'email_verified_at' => now(),
        'idregional' => factory('App\Regional'),
        'idperfil' => factory('App\Perfil'),
        'password' => bcrypt('Teste102030'),
        'remember_token' => Str::random(10),
        'created_at' => now(),
        'updated_at' => now()
    ];
});

$factory->afterCreating(User::class, function ($user, $faker) {
    factory('App\Sessao')->create([
        'idusuario' => $user->idusuario,
        'updated_at' => '2023-05-22 05:20:33'
    ]);
});
