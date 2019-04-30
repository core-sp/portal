<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Permissao;

class ControleController extends Controller
{
    public static function autorizacao($perfis)
    {
        if(!in_array(session('perfil'), $perfis)){
            abort(401);
        }
    }

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
        } else {
            abort(401);
        }
    }

    public static function mostra($perfis)
    {
        if(in_array(session('perfil'), $perfis)){
            return true;
        }
    }
}
