<?php

namespace App\Contracts;

interface TermoConsentimentoServiceInterface {

    public function save($ip, $email);

    public function uploadFile($dados, $tipo_servico, $user);
    
    public function caminhoFile($tipo_servico = null);

    public function dataAtualizacaoTermoStorage($tipo_servico = null);

    public function download();
}