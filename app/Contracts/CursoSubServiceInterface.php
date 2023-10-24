<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface CursoSubServiceInterface {

    public function tiposInscricao();

    public function getTotalInscritos();

    public function getRegrasCampoAdicional($id);

    public function listar($curso, $user);

    public function view($curso = null, $id = null);

    public function save($validated, $user, $curso = null, $id = null);

    public function buscar($curso, $busca, $user);

    public function destroy($id);

    public function updatePresenca($id, $validated);

    public function liberarInscricao($curso, $rep = null, $situacao = '');

    public function inscricaoExterna($curso, $validated = null);
}