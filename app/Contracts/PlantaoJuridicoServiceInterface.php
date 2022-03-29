<?php

namespace App\Contracts;

interface PlantaoJuridicoServiceInterface {

    public function listar();

    public function listarBloqueios();

    public function visualizar($id);

    public function visualizarBloqueio($id = null);

    public function save($request, $id);

    public function saveBloqueio($request, $id = null);

    public function getDatasHorasLinkPlantaoAjax($id);

    public function destroy($id);

    public function plantaoJuridicoAtivo();

    public function getRegionaisDesativadas();

    public function getPlantaoAtivoComBloqueioPorRegional($idregional);

    public function validacaoAgendarPlantao($plantao, $diaEscolhido, $agendados = null, $horaEscolhida = null);
}