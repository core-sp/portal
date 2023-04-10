<?php

namespace App\Contracts;

use App\User;

interface LicitacaoServiceInterface {

    public function getModalidades();

    public function getSituacoes();

    public function listar(User $user);

    public function view($id = null);

    public function save($request, User $user, $id = null);

    public function destroy($id);

    public function lixeira();

    public function restore($id);

    public function buscar(User $user, $busca);

    public function grid($request = null);

    public function show($id);
}