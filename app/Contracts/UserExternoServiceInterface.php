<?php

namespace App\Contracts;

use App\Contabil;
use Illuminate\Foundation\Auth\User as Authenticatable;
// Sendo usados somente UserExterno e Contabil

interface UserExternoServiceInterface {

    public function getDefinicoes($tipo);
    
    public function save($dados);

    public function verificaEmail($token, $tipo);

    public function editDados($dados, Authenticatable $externo, $tipo);

    public function findByCpfCnpj($tipo, $cpf_cnpj);

    public function verificaSeAtivo($tipo, $cpf_cnpj);

    public function cadastroPrevio($contabil, $dados_externo, $retorno_previo = false);
}