<?php

namespace App\Contracts;

use App\User;

interface SuporteServiceInterface {

    public function indexLog();

    public function logBusca($request);

    public function logPorData($data, $tipo);

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

    public function caminhoFileManual($file, User $user);
}