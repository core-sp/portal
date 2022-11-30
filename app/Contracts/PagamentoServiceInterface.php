<?php

namespace App\Contracts;

interface PagamentoServiceInterface {

    public function getDados3DS($bin);

    public function autenticacao3DS($dados, $nome_rota);
    
    public function checkout($ip, $dados, $user);

    public function cancelCheckout($dados, $user);

    public function checkoutIframe($request, $user);

    public function getException($erro_msg, $cod);

    public function rotinaUpdateTransacao($dados);

    // Admin ++++++++++++++++++++++++++++++++++++++++++++
    public function listar();

    public function buscar($busca);
}