<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\User;

interface FiscalizacaoServiceInterface {

    public function listar(User $user);

    public function view($id = null);

    public function save($validated, MediadorServiceInterface $service = null, $id = null);

    public function updateStatus($id);

    public function buscar(User $user, $busca);

    public function mapaSite($id = null);
}