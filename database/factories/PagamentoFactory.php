<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Pagamento;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(Pagamento::class, function (Faker $faker) {
    return [
        'payment_id' => $faker->uuid,
        'cobranca_id' => '1',
        'total' => '2,00',
        'forma' => 'credit',
        'parcelas' => '1',
        'tipo_parcelas' => 'FULL',
        'bandeira' => 'visa',
        'combined_id' => null,
        'payment_tag' => null,
        'is_3ds' => false,
        'status' => 'APPROVED',
        'authorized_at' => Carbon::now('UTC'),
        'canceled_at' => null,
        'gerenti_ok' => true,
        'transacao_temp' => null,
        'idrepresentante' => factory('App\Representante'),
    ];
});

$factory->state(Pagamento::class, 'combinado', function (Faker $faker) {
    $combined_id = $faker->uuid;
    factory('App\Pagamento')->create([
        'combined_id' => $combined_id,
        'payment_tag' => 'pay-1',
    ]);

    return [
        'combined_id' => $combined_id,
        'payment_tag' => 'pay-2',
    ];
});

$factory->state(Pagamento::class, 'cancelado', function (Faker $faker) {
    return [
        'status' => 'CANCELED',
        'canceled_at' => Carbon::now('UTC'),
    ];
});

$factory->state(Pagamento::class, 'parcelado', function (Faker $faker) {
    return [
        'parcelas' => '3',
        'tipo_parcelas' => 'INSTALL_NO_INTEREST',
    ];
});