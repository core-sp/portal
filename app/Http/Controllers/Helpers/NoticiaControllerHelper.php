<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NoticiaControllerHelper extends Controller
{
    public static function categorias()
    {
        $array = [
            'Feiras',
            'Benefícios'
        ];
        return $array;
    }
}
