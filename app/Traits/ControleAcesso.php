<?php

namespace App\Traits;

use App\Repositories\PermissaoRepository;
use Illuminate\Support\Facades\Auth;

trait ControleAcesso {
    public function autoriza($controller, $metodo)
    {
        $string = (new PermissaoRepository())->getFirst($controller, $metodo);
        if(isset($string)) {
            $array = explode(',',$string->perfis);
            if(!in_array(Auth::user()->perfil->idperfil, $array)){
                abort(403);
            }
            return response(100);
        } else {
            abort(403);
        }
    }

    public function mostra($controller, $metodo)
    {
        $string = (new PermissaoRepository())->getFirst($controller, $metodo);
        $perfis = explode(',',$string->perfis);
        if(!in_array(Auth::user()->perfil->idperfil, $perfis)){
            return false;
        }
        return true;
    }

    public function autorizaStatic($perfis)
    {
        if(!in_array(Auth::user()->perfil->idperfil, $perfis)){
            abort(403);
        }
    }

    public function mostraStatic($perfis)
    {
        if(in_array(Auth::user()->perfil->idperfil, $perfis)){
            return true;
        }
    }
}