<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\User;

interface PaginaServiceInterface {

    public function listar(User $user);

    public function view($id = null);

    public function save($request, User $user, $id = null);

    public function destroy($id);

    public function lixeira();

    public function restore($id);

    public function buscar(User $user, $busca);

    public function show($slug);

    public function buscaSite($busca);
}