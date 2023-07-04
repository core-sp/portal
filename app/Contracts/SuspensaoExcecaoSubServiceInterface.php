<?php

namespace App\Contracts;

interface SuspensaoExcecaoSubServiceInterface {

    public function listar($user);

    public function view($user, $id = null);

    public function save($user, $dados, $id = null);

    public function verificaSuspenso($cpf_cnpj);
}