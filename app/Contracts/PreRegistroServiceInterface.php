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

    public function downloadAnexo($id, $externo);

    public function excluirAnexo($id, $externo);

    // ADMIN
    public function getTiposAnexos();

    public function listar($request, MediadorServiceInterface $service, $user);

    public function view($id);

    public function buscar($busca);

    public function saveAjaxAdmin($request, $id, $user);

    public function updateStatus($id, $user, $situacao);
}