<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Curso;
use App\CursoInscrito;

class CursoHelper extends Controller
{
    public static function tipos()
    {
        $tipos = [
            'Palestra',
            'Curso',
            'Workshop'
        ];
        return $tipos;
    }

    public static function getData($data)
    {
    	$date = new \DateTime($data);
    	$format = $date->format('Y-m-d\TH:i:s');
    	return $format;
    }

    public static function contagem($idcurso)
    {
        $contagem = CursoInscrito::where('idcurso', $idcurso)->count();
        return $contagem;
    }

    public static function totalInscritos()
    {
        return CursoInscrito::all()->count();
    }
}
