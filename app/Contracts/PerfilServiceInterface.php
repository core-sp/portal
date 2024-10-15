<?php

namespace App\Contracts;

interface PerfilServiceInterface {

    public function all();

    public function permissoesAgrupadasPorController();

    public function listar();

    public function view($id = null);

    public function save($dados, $id = null);

    public function delete($id);
}
