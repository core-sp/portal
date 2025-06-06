<?php

namespace App\Contracts;

interface SuporteServiceInterface {

    public function indexLog();

    public function logBusca($request);

    public function logPorData($data, $tipo);

    public function verificaHashLog($data, $tipo);

    public function relatorios($dados, $acao = null);

    public function relatorioFinal();

    public function filtros();

    public function indexErros();

    public function uploadFileErros($file);

    public function getFileErros();

    public function ipsBloqueados($ip = null);

    public function ips();

    public function bloquearIp($ip);

    public function liberarIp($ip, $user = null);

    public function sobreStorage();
}