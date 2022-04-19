<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface AgendamentoServiceInterface {

    public function listar($request = null, MediadorServiceInterface $service = null);

    public function listarBloqueio();

    public function view($id);

    public function viewBloqueio($id = null, MediadorServiceInterface $service = null);

    public function viewSite(MediadorServiceInterface $service);

    public function save($dados, $id = null);

    public function saveBloqueio($dados, MediadorServiceInterface $service, $id = null);

    public function saveSite($dados, MediadorServiceInterface $service);

    public function consultaSite($dados);

    public function cancelamentoSite($dados);

    public function delete($id);

    public function enviarEmail($id);

    public function buscar($busca);

    public function getServicosOrStatusOrCompletos($tipo);

    public function countAll();

    public function pendentesByPerfil($count = true);

    public function getDiasHorasAjaxSite($dados, MediadorServiceInterface $service);

    // PLANTÃO JURÍDICO - melhorar quando refatorar AgendamentoSite

    // public function countPlantaoJuridicoByCPF($cpf, $regional, $plantao);

    // -----------------------------------------------------------------

    // Momentaneo até refatorar AgendamentoSite
    // public function getByRegional($idregional);
}