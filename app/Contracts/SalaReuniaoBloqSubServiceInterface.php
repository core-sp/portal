<?php

namespace App\Contracts;

interface SalaReuniaoBloqSubServiceInterface {

    public function listar($user);

    public function view($user, $service = null, $id = null);

    public function save($user, $dados, $id = null);

    public function destroy($id);

    public function buscar($busca, $user);
}