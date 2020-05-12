<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Permissao;

trait ControleAcesso {
    public function autoriza($controller, $metodo)
    {
        $string = Permissao::select('perfis')
            ->where('controller',$controller)
            ->where('metodo',$metodo)
            ->first();
        if(isset($string)) {
            $array = explode(',',$string->perfis);
            if(!in_array(Auth::user()->perfil->idperfil, $array)){
                abort(401);
            }
            return response(100);
        } else {
            abort(401);
        }
    }

    public function mostra($controller, $metodo)
    {
        $string = Permissao::select('perfis')
            ->where('controller',$controller)
            ->where('metodo',$metodo)
            ->first();
        $perfis = explode(',',$string->perfis);
        if(!in_array(Auth::user()->perfil->idperfil, $perfis)){
            return false;
        }
        return true;
    }

    public function autorizaStatic($perfis)
    {
        if(!in_array(Auth::user()->perfil->idperfil, $perfis)){
            abort(401);
        }
    }

    public function mostraStatic($perfis)
    {
        if(in_array(Auth::user()->perfil->idperfil, $perfis)){
            return true;
        }
    }
}