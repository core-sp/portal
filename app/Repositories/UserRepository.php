<?php

namespace App\Repositories;

use App\User;

class UserRepository {

    public function getAtendentesByRegional($idregional)
    {
        return User::select('idusuario', 'nome')
            ->where('idregional', $idregional)
            ->whereHas('perfil', function($q) {
                $q->where('idperfil', 8);
            })->get();
    }
}