<?php

namespace App\Contracts;

use App\User;

interface RegionalServiceInterface {

    public function listar(User $user);

    public function view($id);

    public function save($validated, $id);

    public function show($id);

    public function buscar(User $user, $busca);

    public function all();

    public function getById($id);

    /**
     * 
     * Métodos abaixo temporários até refatorar suas respectivas classes
     * Apenas copia e cola do repositório
     * 
    */

    public function getByName($regional);

    public function getRegionais();
}