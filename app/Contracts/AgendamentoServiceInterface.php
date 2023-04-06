<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;
use App\User;

interface AgendamentoServiceInterface {

    // Métodos do Admin
    public function listar(User $user, $request = null, MediadorServiceInterface $service = null);

    public function view(User $user, $id);

    public function enviarEmail(User $user, $id);

    public function save(User $user, $dados, $id = null);

    public function buscar(User $user, $busca);

    public function getServicosOrStatusOrCompletos($tipo);

    public function countAll();

    public function pendentesByPerfil(User $user, $count = true);

    // Retorna o servico com os métodos do Site
    public function site();

    // Retorna o servico com os métodos do Bloqueio
    public function bloqueio();
}