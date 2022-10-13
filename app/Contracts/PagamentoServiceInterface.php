<?php

namespace App\Contracts;

interface PagamentoServiceInterface {

    // em testes
    public function getToken();

    public function pagamentoDebito($ip);

    public function pagamentoCredito($ip, $rep);

    public function cancelarPagamentoCredito($ip);

    public function formatPagCheckout($request);
}