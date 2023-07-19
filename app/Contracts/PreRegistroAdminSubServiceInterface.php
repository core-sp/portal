<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\User;

interface PreRegistroAdminSubServiceInterface {

    public function getTiposAnexos($idPreRegistro);

    public function listar($request, MediadorServiceInterface $service, User $user);

    public function view($id);

    public function buscar($busca);

    public function saveAjaxAdmin($request, $id, User $user);

    public function updateStatus($id, User $user, $status);

    public function executarRotina();
}