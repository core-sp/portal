<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface PagamentoServiceInterface {

    public function getTiposPagamento();

    public function getTiposPagamentoCheckout();

    public function getDados3DS($bin);

    public function autenticacao3DS($dados, $nome_rota);
    
    public function checkout($ip, $dados, $user);

    public function cancelCheckout($dados, $user);

    public function checkoutIframe($request, $user);

    public function rotinaUpdateTransacao($dados, MediadorServiceInterface $service);

    // Admin ++++++++++++++++++++++++++++++++++++++++++++
    public function listar();

    public function buscar($busca);
}