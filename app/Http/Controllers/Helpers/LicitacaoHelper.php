<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LicitacaoHelper extends Controller
{
    public static function modalidades()
    {
        $modalidades = [
            'Carta Convite',
            'Concorrência - Lei 14.133/2021',
            'Concorrência Pública',
            'Concurso',
            'Concurso - Lei 14.133/2021',
            'Cotação Eletrônica',
            'Credenciamento',
            'Diálogo Competitivo - Lei 14.133/2021',
            'Dispensa Eletrônica - Lei 14.133/2021',
            'Inexigibilidade - Lei 14.133/2021',
            'Leilão',
            'Leilão - Lei 14.133/2021',
            'Pregão - Lei 14.133/2021',
            'Pregão Eletrônico SRP',
            'Pregão Eletrônico Tradicional',
            'Pregão Presencial',
            'Tomada de Preços'
        ];
        return $modalidades;
    }

    public static function situacoes()
    {
    	$situacoes = [
            'Aberto',
            'Adjudicado',
            'Anulado',
            'Apresentação de Propostas e Lances',
            'Cancelado',
            'Concluído',
            'Deserto',
            'Divulgação do Edital de Licitação',
            'Em Andamento',
            'Em fase de recurso',
            'Encerrado',
            'Fase interna',
            'Homologado',
            'Julgamento',
            'Preparatória',
            'Suspenso'
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
