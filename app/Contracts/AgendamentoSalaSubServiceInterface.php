<?php

namespace App\Contracts;

interface AgendamentoSalaSubServiceInterface {

    public function listar($user, $temFiltro = null, $request = null, $service = null);

    public function view($user = null, $id = null, $anexo = null);

    public function save($dados, $user);

    public function update($user, $id, $acao, $justificativa = ['justificativa_admin' => null]);

    public function buscar($user, $busca);

    public function executarRotinaAgendadosDoDia($users);

    public function executarRotina();

    public function executarRotinaRemoveAnexos();
}