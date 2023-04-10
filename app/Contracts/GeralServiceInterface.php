<?php

namespace App\Contracts;

use App\User;

interface GeralServiceInterface {

    // Gerencia o carrossel da homepage
    public function carrossel($dados = null);
}