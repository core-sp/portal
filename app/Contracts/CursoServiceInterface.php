<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface CursoServiceInterface {

    public function tipos();

    public function acessos();

    public function rotulos();

    public function getRegrasCampoAdicional($id);

    public function listar($user);

    public function view($id = null);

    public function save($validated, $user, $id = null);

    public function destroy($id);

    public function lixeira();

    public function restore($id);

    public function buscar($busca, $user);

    public function downloadInscricoes($id);

    public function show($id, $publicado = false);

    public function siteGrid();

    public function cursosAnteriores();

    public function certificadosRepresentante($cpf_cnpj);

    public function inscritos();
}