<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\Repositories\GerentiRepositoryInterface;
use App\UserExterno;

interface PreRegistroServiceInterface {

    public function getNomesCampos();
    
    public function verificacao(GerentiRepositoryInterface $gerentiRepository, UserExterno $externo);
    
    public function getPreRegistro(MediadorServiceInterface $service, UserExterno $externo);

    public function saveSiteAjax($request, GerentiRepositoryInterface $gerentiRepository, UserExterno $externo);

    public function saveSite($request, GerentiRepositoryInterface $gerentiRepository, UserExterno $externo);

    public function downloadAnexo($id, $idPreRegistro);

    public function excluirAnexo($id, UserExterno $externo);

    // Retorna os método do admin conforme o Contract PreRegistroAdminSubServiceInterface
    public function admin();
}