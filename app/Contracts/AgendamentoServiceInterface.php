<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface AgendamentoServiceInterface {

    public function index($request, MediadorServiceInterface $service);

    public function view($id);

    public function save($dados, $id);

    public function updateStatus($dados);

    public function pendentes();

    public function buscar($busca);
}