<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\User;

interface AgendamentoBloqueioSubServiceInterface {

    public function listar(User $user);

    public function view($id = null, MediadorServiceInterface $service = null);

    public function save(User $user, $dados, MediadorServiceInterface $service, $id = null);

    public function delete($id);

    public function buscar(User $user, $busca);
}