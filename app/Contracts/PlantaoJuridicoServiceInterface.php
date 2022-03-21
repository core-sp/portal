<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface PlantaoJuridicoServiceInterface {

    public function listar();

    public function visualizar($id, MediadorServiceInterface $service);

    public function save($request, $id);

    public function listarBloqueios();

    public function visualizarBloqueio($id = null);

    public function getDatasHorasLinkPlantaoAjax($id);

    public function saveBloqueio($request, $id = null);

    public function destroy($id);

    public function plantaoJuridicoAtivo();

    public function getRegionaisDesativadas();

    public function getPlantaoAtivoComBloqueioPorRegional($idregional);

    public function removeHorariosSeLotado($agendados, $plantao, $dia);

    public function getDiasSeLotado($agendados, $plantao);

    public function validacaoAgendarPlantao($plantao, $diaEscolhido, $agendados = null, $horaEscolhida = null);
}