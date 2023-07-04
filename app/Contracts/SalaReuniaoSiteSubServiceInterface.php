<?php

namespace App\Contracts;

interface SalaReuniaoSiteSubServiceInterface {

    public function verificaPodeAgendar($user, $service, $mes = null, $ano = null);
    
    public function save($dados, $user, $service);

    public function verificaPodeEditar($id, $user);

    public function editarParticipantes($dados, $id, $user);

    public function verificaPodeCancelar($id, $user);

    public function cancelar($id, $user);

    public function verificaPodeJustificar($id, $user);

    public function justificar($dados, $id, $user);

    public function participantesVetados($dia, $periodo, $array_cpfs, $id = null);

    public function getAgendadosParticipante($user);
}