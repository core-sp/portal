<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\Repositories\GerentiRepositoryInterface;
use App\UserExterno;
use App\Contabil;

interface PreRegistroServiceInterface {

    public function verificacao(GerentiRepositoryInterface $gerentiRepository, UserExterno $externo);

    public function setPreRegistro(GerentiRepositoryInterface $gerentiRepository, MediadorServiceInterface $service, Contabil $contabil, $dados);

    public function getPreRegistros(Contabil $contabil);
    
    public function getPreRegistro(MediadorServiceInterface $service, UserExterno $externo);

    public function saveSiteAjax($request, GerentiRepositoryInterface $gerentiRepository, UserExterno $externo, Contabil $contabil = null);

    public function saveSite($request, GerentiRepositoryInterface $gerentiRepository, UserExterno $externo, Contabil $contabil = null);

    public function downloadAnexo($id, $idPreRegistro, $admin = false);

    public function excluirAnexo($id, UserExterno $externo, Contabil $contabil = null);

    // Retorna os método do admin conforme o Contract PreRegistroAdminSubServiceInterface
    public function admin();
}