<?php

namespace App\Contracts;

// Temporário até refatorar o Agendamento no Service
use App\Repositories\AgendamentoRepository;

interface PlantaoJuridicoServiceInterface {

    public function listar();

    public function visualizar($id, AgendamentoRepository $agendamento);

    public function save($request, $id);

    public function listarBloqueios();

    public function visualizarBloqueio($id = null);

    public function getDatasHorasPlantaoAjax($id);

    public function saveBloqueio($request, $id = null);

    public function destroy($id);

    public function plantaoJuridicoAtivo();

    public function getRegionaisDesativadas();

    public function getPlantaoAtivoComBloqueioPorRegional($idregional);

    public function removeHorariosSeLotado($agendados, $plantao, $dia);

    public function getDiasSeLotado($agendados, $plantao);

    public function validacaoAgendarPlantao($plantao, $diaEscolhido, $agendados = null, $horaEscolhida = null);
}