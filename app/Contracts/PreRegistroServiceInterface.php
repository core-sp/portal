<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\Repositories\GerentiRepositoryInterface;
use App\UserExterno;
use App\Contabil;

interface PreRegistroServiceInterface {

    public function getNomesCampos();
    
    public function verificacao(GerentiRepositoryInterface $gerentiRepository, UserExterno $externo);

    public function setPreRegistro(GerentiRepositoryInterface $gerentiRepository, MediadorServiceInterface $service, Contabil $externo, $dados);

    public function getPreRegistros(Contabil $externo);
    
    public function getPreRegistro(MediadorServiceInterface $service, UserExterno $externo);

    public function saveSiteAjax($request, GerentiRepositoryInterface $gerentiRepository, UserExterno $externo, Contabil $contabil = null);

    public function saveSite($request, GerentiRepositoryInterface $gerentiRepository, UserExterno $externo, Contabil $contabil = null);

    public function downloadAnexo($id, $idPreRegistro, Contabil $contabil = null);

    public function excluirAnexo($id, UserExterno $externo, Contabil $contabil = null);

    // Retorna os método do admin conforme o Contract PreRegistroAdminSubServiceInterface
    public function admin();
}