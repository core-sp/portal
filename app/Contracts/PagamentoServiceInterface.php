<?php

namespace App\Contracts;

interface PagamentoServiceInterface {

    public function getToken();

    public function formatPagCheckout($request);
}