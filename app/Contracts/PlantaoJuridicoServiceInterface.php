<?php

namespace App\Contracts;

interface PlantaoJuridicoServiceInterface {

    public function listar();

    public function visualizar($id);

    public function save($request, $id);

    public function listarBloqueios();

    public function visualizarBloqueio($id = null);

    public function getDatasHorasPlantaoAjax($id);

    public function saveBloqueio($request, $id = null);

    public function destroy($id);

    public function plantaoJuridicoAtivo();

    public function getRegionaisDesativadas();

    public function getPlantaoPorRegional($idregional);

    public function removeHorariosSeLotado($agendados, $plantao, $dia);

    public function getDiasSeLotado($agendados, $plantao);
}