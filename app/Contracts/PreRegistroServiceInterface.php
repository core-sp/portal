<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\Repositories\GerentiRepositoryInterface;

interface PreRegistroServiceInterface {

    public function getNomesCampos();
    
    public function verificacao(GerentiRepositoryInterface $gerentiRepository, $externo);
    
    public function getPreRegistro(MediadorServiceInterface $service, $resultado, $externo);

    public function saveSiteAjax($request, GerentiRepositoryInterface $gerentiRepository, $externo);

    public function saveSite($request, GerentiRepositoryInterface $gerentiRepository, $externo);

    public function downloadAnexo($id, $externo);

    public function excluirAnexo($id, $externo);
}