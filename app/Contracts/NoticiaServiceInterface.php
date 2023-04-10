<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\User;

interface NoticiaServiceInterface {

    public function getCategorias();

    public function listar(User $user);

    public function view(MediadorServiceInterface $service, $id = null);

    public function save($request, User $user, $id = null);

    public function destroy($id);

    public function lixeira();

    public function restore($id);

    public function buscar(User $user, $busca);

    public function show($slug);

    public function grid();

    public function buscaSite($busca);

    public function latest();

    public function latestByCategoria($categoria);
}