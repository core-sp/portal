<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface AgendamentoServiceInterface {

    // Métodos do Admin
    public function listar($request = null, MediadorServiceInterface $service = null);

    public function view($id);

    public function enviarEmail($id);

    public function save($dados, $id = null);

    public function buscar($busca);

    public function getServicosOrStatusOrCompletos($tipo);

    public function countAll();

    public function pendentesByPerfil($count = true);

    // Retorna o servico com os métodos do Site
    public function site();

    // Retorna o servico com os métodos do Bloqueio
    public function bloqueio();
}