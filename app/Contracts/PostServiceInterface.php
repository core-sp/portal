<?php

namespace App\Contracts;

interface PostServiceInterface {

    public function listar();

    public function view($id = null);

    public function save($request, $user, $id = null);

    public function destroy($id);

    public function buscar($busca);

    public function viewSite($slug);

    public function siteGrid();

    public function buscaSite($busca);

    public function latest();
}