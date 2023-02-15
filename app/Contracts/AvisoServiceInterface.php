<?php

namespace App\Contracts;

interface AvisoServiceInterface {

    public function componente();

    public function cores();

    public function listar();

    public function show($id);

    public function edit($id);

    public function save($validated, $id, $user);

    public function updateStatus($id, $user);

    public function getByArea($area);
    
    public function avisoAtivado($area);
}