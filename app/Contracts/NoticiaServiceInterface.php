<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface NoticiaServiceInterface {

    public function getCategorias();

    public function listar();

    public function view(MediadorServiceInterface $service, $id = null);

    public function save($request, $user, $id = null);

    public function destroy($id);

    public function lixeira();

    public function restore($id);

    public function buscar($busca);

    public function viewSite($slug);

    public function siteGrid();

    public function buscaSite($busca);

    public function latest();

    public function latestByCategoria($categoria);
}