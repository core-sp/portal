<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface AgendamentoServiceInterface {

    public function listar($request = null, MediadorServiceInterface $service = null);

    public function listarBloqueio();

    public function view($id);

    public function viewBloqueio($id = null, MediadorServiceInterface $service = null);

    public function save($dados, $id = null);

    public function saveBloqueio($dados, $id = null);

    public function delete($id);

    public function enviarEmail($id);

    public function buscar($busca);

    public function getServicosOrStatusOrCompletos($tipo);

    public function countAll();

    public function pendentesByPerfil($count = true);

    // PLANTÃO JURÍDICO - melhorar quando refatorar AgendamentoSite

    public function getPlantaoJuridicoByRegionalAndDia($regional, $dia);

    public function countPlantaoJuridicoByCPF($cpf, $regional, $plantao);

    public function getPlantaoJuridicoPorPeriodo($regional, $dataInicial, $dataFinal);

    // -----------------------------------------------------------------

    // Momentaneo até refatorar AgendamentoSite
    public function getByRegional($idregional);
}