<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface AgendamentoBloqueioSubServiceInterface {

    public function listar();

    public function view($id = null, MediadorServiceInterface $service = null);

    public function save($dados, MediadorServiceInterface $service, $id = null);

    public function delete($id);

    public function buscar($busca);
}