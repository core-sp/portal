<?php

namespace App\Contracts;

interface SalaReuniaoServiceInterface {

    public function getItensByTipo($tipo);

    public function listar();

    public function view($id);

    public function save($dados, $id, $user);

    public function salasAtivas($tipo = null);

    public function getDiasHoras($tipo, $id, $dia = null);
}