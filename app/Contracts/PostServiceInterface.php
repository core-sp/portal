<?php

namespace App\Contracts;

use App\User;

interface PostServiceInterface {

    public function listar(User $user);

    public function view($id = null);

    public function save($request, $user, $id = null);

    public function destroy($id);

    public function buscar(User $user, $busca);

    public function show($slug);

    public function grid();

    public function buscaSite($busca);

    public function latest();
}