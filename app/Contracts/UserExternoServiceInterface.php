<?php

namespace App\Contracts;

interface UserExternoServiceInterface {

    public function save($dados);

    public function verificaEmail($token);

    public function editDados($dados, $externo);

    public function findByCpfCnpj($cpf_cnpj);
}