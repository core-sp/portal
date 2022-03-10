<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface AgendamentoServiceInterface {

    public function index($request, MediadorServiceInterface $service);

    public function buscar($busca);

    public function view($id);
}