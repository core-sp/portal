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
        'agendamento_sala_id' => null,
        'justificativa' => json_encode([
            '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - '.$faker->sentence(100).' Data da justificativa: ' . formataData(now())
        ], JSON_FORCE_OBJECT),
        'idusuario' => $user->idusuario,
    ];
});

$factory->state(SuspensaoExcecao::class, 'request_suspensao', function (Faker $faker) {
    return [
        'idrepresentante' => null,
        'justificativa' => $faker->sentence(100),
        'situacao' => null,
        'idusuario' => null,
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

$factory->state(SuspensaoExcecao::class, 'justificativa_recusada', function (Faker $faker) {
    $user = auth()->guard('web')->id() !== null ? auth()->guard('web')->user() : factory('App\User')->create();
    $agendamento = factory('App\AgendamentoSala')->states('recusado')->create();

    $texto = '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - Após análise da justificativa enviada pelo representante, o agendamento com o protocolo '. $agendamento->protocolo;
    $texto .= ' teve o status atualizado para ' . $agendamento::STATUS_NAO_COMPARECEU . ' devido a recusa.';
    $texto .= ' A justificativa do funcionário foi enviada por e-mail para o representante e está no agendamento. Então, o CPF / CNPJ ';

    return [
        'agendamento_sala_id' => $agendamento->id,
        'justificativa' => json_encode([$texto], JSON_FORCE_OBJECT),
    ];
});

$factory->afterCreating(SuspensaoExcecao::class, function ($suspensao, $faker) {
    $rc = \App\Representante::where('cpf_cnpj', $suspensao->cpf_cnpj)->first();
    if(isset($rc))
        $suspensao->updateRelacaoByIdRep($rc->id);
    elseif($suspensao->cpf_cnpj == '86294373085')
        $suspensao->updateRelacaoByIdRep(factory('App\Representante')->create()->id);
});
