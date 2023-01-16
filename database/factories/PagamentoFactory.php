<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Pagamento;
use Faker\Generator as Faker;
use App\Representante;

$factory->define(Pagamento::class, function (Faker $faker) {
    return [
        'payment_id' => $faker->uuid,
        'cobranca_id' => (string) $faker->numberBetween(100, 1000),
        'total' => '2,00',
        'forma' => 'credit',
        'parcelas' => '1',
        'tipo_parcelas' => 'FULL',
        'bandeira' => 'visa',
        'combined_id' => null,
        'payment_tag' => null,
        'total_combined' => null,
        'is_3ds' => false,
        'status' => 'APPROVED',
        'authorized_at' => now()->toIso8601ZuluString(),
        'canceled_at' => null,
        'gerenti_ok' => true,
        'transacao_temp' => null,
        'idrepresentante' => Representante::first() !== null ? Representante::first() : factory('App\Representante'),
    ];
});

$factory->state(Pagamento::class, 'combinado_autorizado', function (Faker $faker) {
    $combined_id = $faker->uuid;
    $pay1 = factory('App\Pagamento')->create([
        'forma' => 'combined',
        'combined_id' => $combined_id,
        'payment_tag' => 'pay-1',
        'total_combined' => '1,00',
        'status' => 'AUTHORIZED'
    ]);

    return [
        'forma' => 'combined',
        'combined_id' => $combined_id,
        'payment_tag' => 'pay-2',
        'total_combined' => '1,00',
        'status' => 'AUTHORIZED'
    ];
});

$factory->state(Pagamento::class, 'combinado_confirmado', function (Faker $faker) {
    $combined_id = $faker->uuid;
    $pay1 = factory('App\Pagamento')->create([
        'forma' => 'combined',
        'combined_id' => $combined_id,
        'payment_tag' => 'pay-1',
        'total_combined' => '1,00',
        'status' => 'CONFIRMED'
    ]);

    return [
        'forma' => 'combined',
        'combined_id' => $combined_id,
        'payment_tag' => 'pay-2',
        'total_combined' => '1,00',
        'status' => 'CONFIRMED'
    ];
});

$factory->state(Pagamento::class, 'cancelado', function (Faker $faker) {
    return [
        'status' => 'CANCELED',
        'canceled_at' => now()->toIso8601ZuluString(),
    ];
});

$factory->state(Pagamento::class, 'parcelado', function (Faker $faker) {
    return [
        'parcelas' => '3',
        'tipo_parcelas' => 'INSTALL_NO_INTEREST',
    ];
});