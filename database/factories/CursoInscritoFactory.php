<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\CursoInscrito;
use Faker\Generator as Faker;

$factory->define(CursoInscrito::class, function (Faker $faker) {
    return [
        'cpf' => '543.848.752-90',
        'nome' => 'Nome Teste Inscrito',
        'telefone' => '(11) 99999-9999',
        'email' => $faker->email,
        'registrocore' => null,
        'tipo_inscrito' => CursoInscrito::INSCRITO_SITE,
        'presenca' => null,
        'idcurso' => factory('App\Curso'),
        'idusuario' => Auth::id() !== null ? Auth::id() : factory('App\User')
    ];
});

$factory->state(CursoInscrito::class, 'representante', function (Faker $faker) {
    $rep = factory('App\Representante')->create();

    return [
        'cpf' => $rep->cpf_cnpj,
        'nome' => $rep->nome,
        'email' => $rep->email,
        'registrocore' => $rep->registro_core,
    ];
});

$factory->state(CursoInscrito::class, 'tipo_convidado', function (Faker $faker) {
    return [
        'tipo_inscrito' => CursoInscrito::INSCRITO_CON,
    ];
});

$factory->state(CursoInscrito::class, 'tipo_funcionario', function (Faker $faker) {
    return [
        'tipo_inscrito' => CursoInscrito::INSCRITO_FUN,
    ];
});

$factory->state(CursoInscrito::class, 'tipo_autoridade', function (Faker $faker) {
    return [
        'tipo_inscrito' => CursoInscrito::INSCRITO_AUT,
    ];
});

$factory->state(CursoInscrito::class, 'tipo_parceiro', function (Faker $faker) {
    return [
        'tipo_inscrito' => CursoInscrito::INSCRITO_PAR,
    ];
});

$factory->state(CursoInscrito::class, 'tipo_site', function (Faker $faker) {
    return [
        'tipo_inscrito' => CursoInscrito::INSCRITO_SITE,
    ];
});