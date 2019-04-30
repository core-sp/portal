<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

class ControleController extends Controller
{
    public static function autorizacao($perfis)
    {
        if(!in_array(session('perfil'), $perfis)){
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
