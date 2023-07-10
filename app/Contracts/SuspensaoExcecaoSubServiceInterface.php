<?php

namespace App\Contracts;

interface SuspensaoExcecaoSubServiceInterface {

    public function listar($user);

    public function view($user, $id = null);

    public function save($user, $dados, $id = null);

    public function buscar($busca, $user);

    public function verificaSuspenso($cpf_cnpj);

    public function participantesSuspensos($cpfs);

    public function executarRotina($service);
}