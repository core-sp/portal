<?php

namespace App\Contracts;

interface PagamentoServiceInterface {

    // em testes
    public function checkout($ip, $dados, $rep);

    public function cancelCheckout($dados, $rep);

    public function bin($bin);

    public function formatPagCheckoutIframe($request);
}