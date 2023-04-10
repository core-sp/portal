<?php

namespace App\Contracts;

use App\Repositories\GerentiRepositoryInterface;
use App\Contracts\MediadorServiceInterface;
use App\Representante;
use App\User;

interface CedulaServiceInterface {

    public function getAllTipos();

    public function getAllStatus();

    public function listar($request);

    public function view($id);

    public function updateStatus($id, $dados, User $user);

    public function gerarPdf($id);

    public function buscar($busca);

    public function getByRepresentante(Representante $user, GerentiRepositoryInterface $gerenti = null);

    public function save($dados, Representante $user, GerentiRepositoryInterface $gerenti, MediadorServiceInterface $service);
}