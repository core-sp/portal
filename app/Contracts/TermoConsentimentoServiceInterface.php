<?php

namespace App\Contracts;

interface TermoConsentimentoServiceInterface {

    public function save($ip, $object);

    public function caminhoFile();

    public function download();
}