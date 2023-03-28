<?php

namespace App\Contracts;

interface SuporteServiceInterface {

    public function indexLog();

    public function logBusca($request);

    public function logPorData($data, $tipo);

    public function ipsBloqueados();

    public function ips();

    public function bloquearIp($ip);

    public function liberarIp($ip, $user = null);

    public function caminhoFileManual($file);
}