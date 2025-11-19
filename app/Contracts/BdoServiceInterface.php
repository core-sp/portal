<?php

namespace App\Contracts;

use App\Repositories\GerentiRepositoryInterface;

interface BdoServiceInterface {

    public function admin();

    public function viewPerfilRC($rep, GerentiRepositoryInterface $gerentiRepository = null);

    public function cadastrarPerfil($rep, $dados, GerentiRepositoryInterface $gerentiRepository);

    public function editarPerfil($rep, $dados);
}