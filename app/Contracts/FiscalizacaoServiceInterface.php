<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface FiscalizacaoServiceInterface {

    public function listar();

    public function view($id = null);

    public function save($validated, MediadorServiceInterface $service = null, $id = null);

    public function updateStatus($id);

    public function buscar($busca);

    public function mapaSite($id = null);
}