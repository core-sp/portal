<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ResponsavelTecnico;
use Faker\Generator as Faker;

$factory->define(ResponsavelTecnico::class, function (Faker $faker) {
    return [
        'cpf' => '47662011089',
        'registro' => null,
        'nome' => 'Nome do RT',
        'nome_social' => null,
        'sexo' => 'F',
        'dt_nascimento' => '1970-02-20',
        'cep' => '03021-050',
        'logradouro' => 'Rua Teste do RT',
        'numero' => '155',
        'complemento' => null,
        'bairro' => 'Teste Bairro',
        'cidade' => 'São Paulo',
        'uf' => 'SP',
        'nome_mae' => 'Nome Mãe do RT',
        'nome_pai' => null,
        'identidade' => '22.111.111-3',
        'orgao_emissor' => 'SSP- SP',
        'dt_expedicao' => '2021-05-20',
    ];
});
