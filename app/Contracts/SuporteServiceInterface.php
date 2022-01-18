<?php

namespace App\Contracts;

interface SuporteServiceInterface {

    public function logDoDia();

    public function indexLog();

    public function busca($request);

    public function logPorData($data);
}