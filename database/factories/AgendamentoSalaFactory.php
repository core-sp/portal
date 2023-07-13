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
        'participantes' => null,
        'dia' => $amanha->format('Y-m-d'),
        'periodo' => 'manha',
        'tipo_sala' => 'coworking',
        'status' => null,
        'justificativa' => null,
        'anexo' => null,
        'sala_reuniao_id' => factory('App\SalaReuniao'),
        'idusuario' => null
    ];
});

$factory->state(AgendamentoSala::class, 'reuniao', function ($faker) {
    return [
        'protocolo' => mb_strtoupper($faker->bothify('RC-AGE-##??##??'), 'UTF-8'),
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