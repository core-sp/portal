<?php

namespace App\Contracts;

interface PlantaoJuridicoBloqueioSubServiceInterface {

    public function listar();

    public function view($id = null);

    public function save($request, $id = null);

    public function getDatasHorasLinkPlantaoAjax($id);

    public function destroy($id);
}