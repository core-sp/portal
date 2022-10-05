<?php

namespace App\Contracts;

interface SuporteServiceInterface {

    public function indexLog();

    public function logBusca($request);

    public function logPorData($data, $tipo);

    public function indexErros();

    public function uploadFileErros($file);

    public function getFileErros();
}