<?php

namespace App\Contracts;

interface TermoConsentimentoServiceInterface {

    public function save($ip, $email);

    public function caminhoFile();

    public function download();
}