<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Permissao;

class ControleController extends Controller
{
    public static function autoriza($controller, $metodo)
    {
        $string = Permissao::select('perfis')
            ->where('controller',$controller)
            ->where('metodo',$metodo)
            ->first();
        if(isset($string)) {
            $array = explode(',',$string->perfis);
            if(!in_array(session('idperfil'), $array)){
                abort(401);
            }
            return response(100);
        } else {
            abort(401);
        }
    }

    public static function mostra($controller, $metodo)
    {
        $cm = $controller.'_'.$metodo;
        $permissoes = session('permissoes');
        if($permissoes) {
            if(in_array($cm, $permissoes))
                return true;
            else
                return false;
        }
    }

    public static function autorizaStatic($perfis)
    {
        if(!in_array(session('idperfil'), $perfis)){
            abort(401);
        }
    }

    public static function mostraStatic($perfis)
    {
        if(in_array(session('idperfil'), $perfis)){
            return true;
        }
    }
}
