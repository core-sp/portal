<?php

use App\TermoConsentimento;
use Faker\Generator as Faker;

$factory->define(TermoConsentimento::class, function (Faker $faker) {
    return [
        'ip' => $faker->ipv4,
        'email' => null,
        'beneficio' => null,
        'idrepresentante' => null,
        'idnewsletter' => null,
        'idagendamento' => null,
        'idbdo' => null,
        'idcursoinscrito' => null,
        'agendamento_sala_id' => null,
        'created_at' => now(),
        'updated_at' => now(),
        'deleted_at' => null
    ];
});

$factory->state(TermoConsentimento::class, 'beneficio', function (Faker $faker) {
    return [
        'beneficio' => 'Allya',
    ];
});
