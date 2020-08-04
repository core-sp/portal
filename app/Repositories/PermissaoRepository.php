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

    /** Remove id de perfil do campo perfis da permissão */
    public function removePerfisById($idPermissao, $idPerfil) 
    {
        $permissao = Permissao::find($idPermissao);
        $permissao->perfis = str_replace($idPerfil.',', '', $permissao->perfis);;
        $permissao->update();
    }

    /** Adiciona id de perfil no campo perfis da permissão */
    public function addPerfisById($idPermissao, $idPerfil) 
    {
        $permissao = Permissao::find($idPermissao);

        //Checando se perfil está presente para evitar ids duplicados
        if(!in_array($idPerfil, explode(',',$permissao->perfis))) {
            $permissao->perfis = $idPerfil.','.$permissao->perfis;
            $permissao->update();
        }
    }
}