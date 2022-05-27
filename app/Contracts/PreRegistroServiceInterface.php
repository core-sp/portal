<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\Repositories\GerentiRepositoryInterface;

interface PreRegistroServiceInterface {

    public function getNomesCampos();
    
    public function verificacao(GerentiRepositoryInterface $gerentiRepository);
    
    public function getPreRegistro(MediadorServiceInterface $service, $resultado);

    public function saveSiteAjax($request, GerentiRepositoryInterface $gerentiRepository);

    public function saveSite($request, GerentiRepositoryInterface $gerentiRepository);

    public function downloadAnexo($id);

    public function excluirAnexo($id);
}