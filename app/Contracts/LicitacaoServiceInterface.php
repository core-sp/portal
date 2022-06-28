<?php

namespace App\Contracts;

interface LicitacaoServiceInterface {

    public function getModalidades();

    public function getSituacoes();

    public function listar();

    public function view($id = null);

    public function save($request, $user, $id = null);

    public function destroy($id);

    public function lixeira();

    public function restore($id);

    public function buscar($busca);

    public function siteGrid($request = null);

    public function viewSite($id);
}