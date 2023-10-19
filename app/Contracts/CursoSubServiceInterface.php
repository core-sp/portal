<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface CursoSubServiceInterface {

    public function tiposInscricao();

    public function listar($curso, $user);

    public function view($curso = null, $id = null);

    public function save($validated, $user, $curso = null, $id = null);

    public function buscar($curso, $busca, $user);

    public function destroy($id);
}