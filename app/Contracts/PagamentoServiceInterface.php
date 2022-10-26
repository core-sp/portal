<?php

namespace App\Contracts;

interface PagamentoServiceInterface {

    // em testes
    public function checkout($ip, $dados, $user);

    public function cancelCheckout($dados, $user);

    public function bin($bin);

    public function formatPagCheckoutIframe($request);

    public function getException($erro_msg, $cod);
}