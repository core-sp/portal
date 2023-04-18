<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\Repositories\GerentiRepositoryInterface;

interface PreRegistroServiceInterface {

    public function getNomesCampos();
    
    public function verificacao(GerentiRepositoryInterface $gerentiRepository, $externo);
    
    public function getPreRegistro(MediadorServiceInterface $service, $externo);

    public function saveSiteAjax($request, GerentiRepositoryInterface $gerentiRepository, $externo);

    public function saveSite($request, GerentiRepositoryInterface $gerentiRepository, $externo);

    public function downloadAnexo($id, $idPreRegistro);

    public function excluirAnexo($id, $externo);

    // Retorna os método do admin conforme o Contract PreRegistroAdminSubServiceInterface
    public function admin();
}