<?php

namespace App\Contracts;

use App\Repositories\GerentiRepositoryInterface;

interface GeralServiceInterface {

    // Gerencia o carrossel da homepage
    public function carrossel($dados = null);

    // Formata os dados do gerenti para a view
    public function consultaSituacao($dados_gerenti);

    // Formata os dados do gerenti para a view
    public function anuidadeVigente($dados_gerenti);

    // Salva newsletter
    public function newsletter($dados);

    // Faz download e devolve total
    public function newsletterAdmin(bool $download = true);

    // Realiza a simulação do registro inicial
    public function simulador($validated = null, GerentiRepositoryInterface $gerenti = null);
}