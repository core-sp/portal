<?php

namespace App\Contracts;

use App\UserExterno;

interface UserExternoServiceInterface {

    public function save($dados);

    public function verificaEmail($token);

    public function editDados($dados, UserExterno $externo);

    public function findByCpfCnpj($cpf_cnpj);

    public function verificaSeAtivo($cpf_cnpj);
}