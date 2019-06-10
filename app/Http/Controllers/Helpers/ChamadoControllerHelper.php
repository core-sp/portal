<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Chamado;

class ChamadoControllerHelper extends Controller
{
    public static function getByUser($idusuario)
    {
        $resultados = Chamado::where('idusuario',$idusuario)->withTrashed()->orderBy('created_at','DESC')->paginate(5);
        return $resultados;
    }

    public static function tipos()
    {
        $tipos = [
            'Dúvida',
            'Reportar Bug',
            'Sugestão',
            'Solicitar Funcionalidade'
        ];
        return $tipos;  
    }

    public static function prioridades()
    {
        $prioridades = [
            'Muito Baixa',
            'Baixa',
            'Normal',
            'Alta',
            'Muito Alta'
        ];
        return $prioridades;
    }
}
