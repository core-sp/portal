<?php

namespace App\Contracts;

use App\User;

interface GeralServiceInterface {

    // Gerencia o carrossel da homepage
    public function carrossel($dados = null);

    // Formata os dados do gerenti para a view
    public function consultaSituacao($dados_gerenti);
}