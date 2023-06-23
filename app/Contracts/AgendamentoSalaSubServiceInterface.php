<?php

namespace App\Contracts;

interface AgendamentoSalaSubServiceInterface {

    public function listar($user, $request = null, $service = null);

    public function view($user, $id, $anexo = null);

    public function update($user, $id, $acao, $justificativa = ['justificativa_admin' => null]);

    public function buscar($user, $busca);
}