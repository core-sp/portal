<?php

namespace App\Contracts;

interface RegionalServiceInterface {

    public function index();

    public function view($id);

    public function save($validated, $id);

    public function viewSite($id);

    public function buscar($busca);

    public function all();
}