<?php

namespace App\Contracts;

interface SalaReuniaoServiceInterface {

    public function getHoras();

    public function getItensByTipo($tipo);

    public function listar();

    public function view($id);

    public function save($dados, $id, $user);

    public function salasAtivas($tipo = null);

    public function getDiasHoras($tipo, $id, $dia = null, $user = null);

    public function getTodasHorasById($id);

    public function site();

    public function agendados();

    public function bloqueio();

    public function suspensaoExcecao();
}