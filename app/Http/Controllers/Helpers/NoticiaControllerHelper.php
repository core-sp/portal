<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Noticia;
use App\Http\Controllers\Controller;

class NoticiaControllerHelper extends Controller
{
    public static function categorias()
    {
        $array = [
            'Benefícios',
            'Cotidiano',
            'Feiras'
        ];
        return $array;
    }

    public static function totalNoticias()
    {
        return Noticia::all()->count();
    }
}
