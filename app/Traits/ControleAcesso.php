<?php

namespace App\Traits;

use App\Repositories\PermissaoRepository;
use Illuminate\Support\Facades\Auth;

trait ControleAcesso {
    public function autoriza($controller, $metodo)
    {
        $cm = $controller.'_'.$metodo;
        $permissoes = session('permissoes');

        if(isset($permissoes)) {
            return in_array($cm, $permissoes) ? response(100) : abort(403);
        }
        else {
            abort(403);
        }

        /**
         * Removendo checagem que verifica no banco de dados e usando checagem na sessão
         * para evitar queries. (Solução usada não atualiza modificações realizadas nas permissões
         * se o usuário não logar novamente no Portal)
         */
        // $string = (new PermissaoRepository())->getFirst($controller, $metodo);
        // if(isset($string)) {
        //     $array = explode(',',$string->perfis);
        //     if(!in_array(Auth::user()->perfil->idperfil, $array)){
        //         abort(403);
        //     }
        //     return response(100);
        // } else {
        //     abort(403);
        // }
    }

    public function mostra($controller, $metodo)
    {
        $cm = $controller.'_'.$metodo;
        $permissoes = session('permissoes');

        if(isset($permissoes)) {
            return in_array($cm, $permissoes);
        }
        else {
            return false;;
        }

        /**
         * Removendo checagem que verifica no banco de dados e usando checagem na sessão
         * para evitar queries. (Solução usada não atualiza modificações realizadas nas permissões
         * se o usuário não logar novamente no Portal)
         */
        // $string = (new PermissaoRepository())->getFirst($controller, $metodo);
        // $perfis = explode(',',$string->perfis);
        // if(!in_array(Auth::user()->perfil->idperfil, $perfis)){
        //     return false;
        // }

        // return true;
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