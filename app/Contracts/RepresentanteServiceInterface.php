<?php

namespace App\Contracts;

use App\Repositories\GerentiRepositoryInterface;

interface RepresentanteServiceInterface {

    public function verificaAtivoAndGerenti($cpfCnpj, GerentiRepositoryInterface $gerenti);

    public function findByCpfCnpj($cpf_cnpj);
}