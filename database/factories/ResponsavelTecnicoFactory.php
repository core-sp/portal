<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ResponsavelTecnico;
use Faker\Generator as Faker;

$factory->define(ResponsavelTecnico::class, function (Faker $faker) {
    $faker->addProvider(new \Faker\Provider\pt_BR\Person($faker));

    return [
        'cpf' => $faker->cpf(false),
        'registro' => null,
        'nome' => str_replace("'", "", mb_strtoupper($faker->name, 'UTF-8')),
        'nome_social' => str_replace("'", "", mb_strtoupper($faker->name, 'UTF-8')),
        'sexo' => 'F',
        'dt_nascimento' => '1970-02-20',
        'cep' => '03021-050',
        'logradouro' => 'RUA TESTE DO RT',
        'numero' => '155B',
        'complemento' => null,
        'bairro' => 'TESTE BAIRRO RT',
        'cidade' => 'SÃO PAULO',
        'uf' => 'SP',
        'nome_mae' => str_replace("'", "", mb_strtoupper($faker->name, 'utf-8')),
        'nome_pai' => str_replace("'", "", mb_strtoupper($faker->name, 'UTF-8')),
        'tipo_identidade' => mb_strtoupper(tipos_identidade()[0], 'UTF-8'),
        'identidade' => '221111113X',
        'orgao_emissor' => 'SSP - SP',
        'dt_expedicao' => '2021-05-20',
        'titulo_eleitor' => '875698541236',
        'zona' => '123',
        'secao' => '12345',
        'ra_reservista' => '789547896325',
    ];
});

$factory->state(ResponsavelTecnico::class, 'low', function (Faker $faker) {
    return [
        'nome' => str_replace("'", "", $faker->name),
        'nome_social' => str_replace("'", "", $faker->name),
        'logradouro' => 'Rua Teste do rt',
        'numero' => '155b',
        'complemento' => null,
        'bairro' => 'Teste Bairro rt',
        'cidade' => 'São Paulo',
        'nome_mae' => str_replace("'", "", $faker->name),
        'nome_pai' => str_replace("'", "", $faker->name),
        'tipo_identidade' => tipos_identidade()[0],
        'identidade' => '221111113x',
        'orgao_emissor' => 'ssp - sp',
    ];
});