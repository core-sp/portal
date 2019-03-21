<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConcursoHelper extends Controller
{
    public static function modalidades()
    {
        $modalidades = [
            'Concurso Público',
            'Processo Seletivo'
        ];
        return $modalidades;
    }

    public static function situacoes()
    {
    	$situacoes = [
            'Aberto',
            'Anulado',
    		'Cancelado',
            'Concluído',
    		'Deserto',
    		'Em Andamento',
    		'Homologado'
    	];
    	return $situacoes;
    }

    public static function getData($data)
    {
    	$date = new \DateTime($data);
    	$format = $date->format('Y-m-d\TH:i:s');
    	return $format;
    }
}
