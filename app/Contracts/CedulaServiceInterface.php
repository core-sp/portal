<?php

namespace App\Contracts;

use App\Repositories\GerentiRepositoryInterface;
use App\Contracts\MediadorServiceInterface;

interface CedulaServiceInterface {

    public function getAllStatus();

    public function listar($request);

    public function view($id);

    public function updateStatus($id, $dados, $user);

    public function gerarPdf($id);

    public function buscar($busca);

    // Migrar métodos abaixo para o futuro servico de representante???
    public function getByRepresentante($user, GerentiRepositoryInterface $gerenti = null);

    public function save($dados, $user, GerentiRepositoryInterface $gerenti, MediadorServiceInterface $service);
}