<?php

namespace App\Contracts;

use App\Repositories\GerentiRepositoryInterface;

interface BdoServiceInterface {

    public function admin();

    public function temp_municipios();

    public function viewPerfilRC($rep, GerentiRepositoryInterface $gerentiRepository = null);

    public function cadastrarPerfil($rep, $dados);

    public function editarPerfil($rep, $dados);

    public function buscarPerfisPublicos($dados, $regionais);
}