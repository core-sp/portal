<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BdoEmpresaControllerHelper extends Controller
{
    public static function segmentos()
    {
        $segmentos = [
            'Palestra',
            'Curso',
            'Workshop'
        ];
        return $segmentos;
    }
}
