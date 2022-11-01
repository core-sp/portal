<?php

namespace App\Contracts;

interface PagamentoServiceInterface {

    // em testes
    public function getDados3DS($bin);

    public function generateToken3DS($request);

    public function authentication3DS($request);

    public function authenticationResults3DS($request);
    
    public function checkout($ip, $dados, $user);

    public function cancelCheckout($dados, $user);

    public function formatPagCheckoutIframe($request, $user);

    public function getException($erro_msg, $cod);
}