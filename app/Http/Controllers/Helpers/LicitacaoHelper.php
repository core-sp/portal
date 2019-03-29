<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LicitacaoHelper extends Controller
{
    public static function modalidades()
    {
        $modalidades = [
            'Concorrência Pública',
            'Concurso',
            'Carta Convite',
            'Leilão',
            'Pregão Eletrônico',
            'Pregão Presencial',
            'Tomada de Preços',
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

    public static function onlyDate($data)
    {
        $date = new \DateTime($data);
        $format = $date->format('d\/m\/Y');
        return $format;
    }

    public static function organizaData($data)
    {
        $date = new \DateTime($data);
        $format = $date->format('H:i\ \d\o \d\i\a d\/m\/Y');
        return $format;
    }
}
