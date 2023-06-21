<?php

namespace App\Contracts;

interface AgendamentoSalaSubServiceInterface {

    public function listar($user, $request = null, $service = null);

    public function view($user, $id, $anexo = null);

    public function update($user, $id, $justificativa = []);

    public function buscar($user, $busca);
}