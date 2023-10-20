<?php

namespace App\Contracts;

use App\Repositories\GerentiRepositoryInterface;

interface RepresentanteServiceInterface {

    public function verificaAtivoAndGerenti($cpfCnpj, GerentiRepositoryInterface $gerenti);

    public function getRepresentanteByCpfCnpj($cpfCnpj);

    public function getDadosInscricaoCurso($rep, GerentiRepositoryInterface $gerenti);
}