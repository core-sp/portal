<?php

namespace App\Contracts;

interface BdoServiceInterface {

    public function admin();

    public function temp_municipios();

    public function viewPerfilRC($rep, $home = true);

    public function cadastrarPerfil($rep, $dados);

    public function editarPerfil($rep, $dados);

    public function buscarPerfisPublicos($dados, $regionais);
}