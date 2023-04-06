<?php

namespace App\Contracts;

use App\User;

interface PlantaoJuridicoServiceInterface {

    public function listar(User $user);

    public function view($id);

    public function save($request, $id);

    public function plantaoJuridicoAtivo();

    public function getRegionaisAtivas();

    // Retorna instância do Bloqueio
    public function bloqueio();
}