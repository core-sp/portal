<?php

namespace App\Contracts;

interface SuspensaoExcecaoSubServiceInterface {

    public function listar($user);

    public function view($user, $id);
}