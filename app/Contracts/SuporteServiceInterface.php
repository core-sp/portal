<?php

namespace App\Contracts;

interface SuporteServiceInterface {

    public function indexLog();

    public function logBusca($request);

    public function logPorData($data, $tipo);

    public function relatorios($dados);

    public function relatorioFinal($dados);

    public function indexErros();

    public function uploadFileErros($file);

    public function getFileErros();

    public function ipsBloqueados();

    public function ips();

    public function bloquearIp($ip);

    public function liberarIp($ip, $user = null);
}