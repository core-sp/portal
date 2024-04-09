<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\User;

interface PreRegistroAdminSubServiceInterface {

    public function tiposDocsAtendente();

    public function getTiposAnexos($idPreRegistro);

    public function listar($request, MediadorServiceInterface $service, User $user, $filtro = null);

    public function view($id);

    public function buscar($busca, User $user);

    public function saveAjaxAdmin($request, $id, User $user);

    public function updateStatus($id, User $user, $status);

    public function uploadDoc($id, $file, $tipo_doc);

    public function getJustificativa($user, $id, $campo, $data_hora = null);

    public function executarRotina();
}