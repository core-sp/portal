<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ControleController;
use App\Permissao;

class MenuController extends Controller
{
    public static function menuMostra()
    {
        $permissoes = Permissao::all();
        $permissoes = $permissoes->toArray();
        return $permissoes;
    }
}
