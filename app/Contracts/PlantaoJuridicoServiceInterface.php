<?php

namespace App\Contracts;

interface PlantaoJuridicoServiceInterface {

    public function listar();

    public function visualizar($id = null);
}