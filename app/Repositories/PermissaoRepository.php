<?php

namespace App\Repositories;

use App\Permissao;

class PermissaoRepository {
    public function getFirst($controller, $metodo)
    {
        return Permissao::select('perfis')
            ->where('controller',$controller)
            ->where('metodo',$metodo)
            ->first();
    }

    public function getAll()
    {
        return Permissao::all();
    }
}