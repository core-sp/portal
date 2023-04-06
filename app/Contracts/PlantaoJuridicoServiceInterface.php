<?php

namespace App\Contracts;

interface PlantaoJuridicoServiceInterface {

    public function listar();

    public function view($id);

    public function save($request, $id);

    public function plantaoJuridicoAtivo();

    public function getRegionaisAtivas();

    // Retorna instância do Bloqueio
    public function bloqueio();
}