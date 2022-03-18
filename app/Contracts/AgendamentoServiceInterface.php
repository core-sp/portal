<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface AgendamentoServiceInterface {

    public function listar($request = null, MediadorServiceInterface $service = null);

    public function view($id);

    public function save($dados, $id = null);

    public function enviarEmail($id);

    public function buscar($busca);

    public function getServicosOrStatusOrCompletos($tipo);

    public function countAll();

    public function pendentesByPerfil($count = true);
}