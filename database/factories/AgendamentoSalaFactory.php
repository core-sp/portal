<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\AgendamentoSala;
use Faker\Generator as Faker;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

$factory->define(AgendamentoSala::class, function (Faker $faker) {
    $amanha = Carbon::tomorrow();

    while($amanha->isWeekend())
        $amanha->addDay();

    return [
        'protocolo' => mb_strtoupper($faker->bothify('RC-AGE-##??##??'), 'UTF-8'),
        'idrepresentante' => auth()->guard('representante')->id() !== null ? auth()->guard('representante')->id() : factory('App\Representante'),
        'rep_presencial' => null,
        'participantes' => null,
        'dia' => $amanha->format('Y-m-d'),
        'periodo' => '09:00 - 10:00',
        'periodo_todo' => 0,
        'tipo_sala' => 'coworking',
        'status' => null,
        'justificativa' => null,
        'anexo' => null,
        'sala_reuniao_id' => factory('App\SalaReuniao'),
        'justificativa_admin' => null,
        'idusuario' => null
    ];
});

$factory->state(AgendamentoSala::class, 'reuniao', function ($faker) {
    return [
        'participantes' => json_encode(['56983238010' => 'NOME PARTICIPANTE UM', '81921923008' => 'NOME PARTICIPANTE DOIS'], JSON_FORCE_OBJECT),
        'tipo_sala' => 'reuniao',
    ];
});

$factory->state(AgendamentoSala::class, 'justificado', function ($faker) {
    return [
        'dia' => now()->format('Y-m-d'),
        'justificativa' => $faker->text(300),
        'status' => AgendamentoSala::STATUS_ENVIADA,
    ];
});

$factory->state(AgendamentoSala::class, 'justificado_com_anexo', function ($faker) {
    return [
        'dia' => now()->format('Y-m-d'),
        'justificativa' => $faker->text(300),
        'status' => AgendamentoSala::STATUS_ENVIADA,
    ];
});

$factory->state(AgendamentoSala::class, 'recusado', function ($faker) {
    return [
        'dia' => now()->format('Y-m-d'),
        'justificativa' => $faker->text(300),
        'status' => AgendamentoSala::STATUS_NAO_COMPARECEU,
        'justificativa_admin' => $faker->text(200),
        'idusuario' => factory('App\User')
    ];
});

$factory->afterCreatingState(AgendamentoSala::class, 'justificado_com_anexo', function ($agendamento, $faker) {
    Storage::fake('local');

    $img = $agendamento->representante->id . '-' . time() . '.png';
    $file = UploadedFile::fake()->image($img);
    $file->storeAs("representantes/agendamento_sala", $img);
    $agendamento->update(['anexo' => $img]);
});

$factory->state(AgendamentoSala::class, 'presencial', function ($faker) {
    $hj = Carbon::today();
    while($hj->isWeekend())
        $hj->subDay();

    $rep = factory('App\Representante')->raw();
    $rep = ['cpf_cnpj' => $rep['cpf_cnpj'], 'nome' => $rep['nome'], 'registro_core' => $rep['registro_core'], 'email' => $rep['email'], 'ass_id' => $rep['ass_id']];

    return [
        'idrepresentante' => null,
        'rep_presencial' => json_encode($rep, JSON_FORCE_OBJECT),
        'dia' => $hj->format('Y-m-d'),
        'status' => AgendamentoSala::STATUS_COMPARECEU,
        'idusuario' => factory('App\User')
    ];
});

$factory->state(AgendamentoSala::class, 'presencial_request_coworking', function ($faker) {
    $hj = Carbon::today();
    while($hj->isWeekend())
        $hj->subDay();

    $rep = factory('App\Representante')->raw();
    $rep = ['cpf_cnpj' => $rep['cpf_cnpj'], 'nome' => $rep['nome'], 'registro_core' => $rep['registro_core'], 'email' => $rep['email'], 'ass_id' => $rep['ass_id']];

    return [
        'idrepresentante' => null,
        'cpf_cnpj' => formataCpfCnpj($rep['cpf_cnpj']),
        'tipo_sala' => 'coworking',
        'dia' => $hj->format('Y-m-d'),
        'periodo_entrada' => '10:00',
        'periodo_saida' => '11:00',
        'nome' => $rep['nome'],
        'registro_core' => $rep['registro_core'],
        'email' => $rep['email'],
        'ass_id' => $rep['ass_id'],
    ];
});

$factory->state(AgendamentoSala::class, 'presencial_request_reuniao', function ($faker) {
    $hj = Carbon::today();
    while($hj->isWeekend())
        $hj->subDay();

    $rep = factory('App\Representante')->raw();
    $rep = ['cpf_cnpj' => $rep['cpf_cnpj'], 'nome' => $rep['nome'], 'registro_core' => $rep['registro_core'], 'email' => $rep['email'], 'ass_id' => $rep['ass_id']];

    return [
        'idrepresentante' => null,
        'cpf_cnpj' => formataCpfCnpj($rep['cpf_cnpj']),
        'tipo_sala' => 'reuniao',
        'dia' => $hj->format('Y-m-d'),
        'periodo_entrada' => '10:00',
        'periodo_saida' => '11:00',
        'participantes_cpf' => ['56983238010', '81921923008'],
        'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
        'nome' => $rep['nome'],
        'registro_core' => $rep['registro_core'],
        'email' => $rep['email'],
        'ass_id' => $rep['ass_id'],
    ];
});