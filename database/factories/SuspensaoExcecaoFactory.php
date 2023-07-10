<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\SuspensaoExcecao;
use Faker\Generator as Faker;

$factory->define(SuspensaoExcecao::class, function (Faker $faker) {
    $user = auth()->guard('web')->id() !== null ? auth()->guard('web')->user() : factory('App\User')->create();

    return [
        'cpf_cnpj' => '86294373085',
        'idrepresentante' => null,
        'data_inicial' => now()->format('Y-m-d'),
        'data_final' => now()->addDays(30)->format('Y-m-d'),
        'data_inicial_excecao' => null,
        'data_final_excecao' => null,
        'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO,
        'agendamento_sala_id' => factory('App\AgendamentoSala'),
        'justificativa' => json_encode([
            '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - '.$faker->sentence(100).' Data da justificativa: ' . formataData(now())
        ], JSON_FORCE_OBJECT),
        'idusuario' => $user->idusuario,
    ];
});

$factory->state(SuspensaoExcecao::class, 'request_suspensao', function (Faker $faker) {
    return [
        'justificativa' => $faker->sentence(100),
        'situacao' => null,
        'idusuario' => null,
        'agendamento_sala_id' => null,
    ];
});

$factory->state(SuspensaoExcecao::class, 'excecao', function (Faker $faker) {
    $user = auth()->guard('web')->id() !== null ? auth()->guard('web')->user() : factory('App\User')->create();
    
    return [
        'justificativa' => json_encode([
            '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - '.$faker->sentence(100).' Data da justificativa: ' . formataData(now()),
            '[Funcionário(a) '.$user->nome.'] | [Ação - exceção] - '.$faker->sentence(50).' Data da justificativa: ' . formataData(now())
        ], JSON_FORCE_OBJECT),
        'situacao' => SuspensaoExcecao::SITUACAO_EXCECAO,
        'data_inicial_excecao' => now()->format('Y-m-d'),
        'data_final_excecao' => now()->format('Y-m-d'),
    ];
});
