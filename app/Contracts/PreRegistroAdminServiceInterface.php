<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface PreRegistroAdminServiceInterface {

    public function getTiposAnexos($idPreRegistro);

    public function listar($request, MediadorServiceInterface $service, $user);

    public function view($id);

    public function buscar($busca);

    public function saveAjaxAdmin($request, $id, $user);

    public function updateStatus($id, $user, $status);
}