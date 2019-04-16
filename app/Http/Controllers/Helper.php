<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Helper extends Controller
{
    public static function toSlug($string)
    {
    	return Str::slug($string, '-');
    }

    public static function firstLetter($string)
    {
    	$string = preg_split("/[\s,_-]+/", $string);
    	$acronym = "";
    	foreach ($string as $letra) {
    		$acronym .= $letra[0];
    	}
    	return $acronym;
    }

    public static function onlyDate($data)
    {
        $date = new \DateTime($data);
        $format = $date->format('d\/m\/Y');
        return $format;
    }

    public static function onlyHour($data)
    {
        $date = new \DateTime($data);
        $format = $date->format('H:i');
        return $format;
    }

    public static function formataData($data)
    {
        $date = new \DateTime($data);
        $format = $date->format('d\/m\/Y, \à\s H:i');
        return $format;
    }

    public static function organizaData($data)
    {
        $date = new \DateTime($data);
        $format = $date->format('H:i\ \d\o \d\i\a d\/m\/Y');
        return $format;
    }

    public static function newsData($data)
    {
        $date = new \DateTime($data);
        $format = $date->format('d\/m\/Y');
        return $format;
    }

    public static function resumo($string)
    {
        if (strlen($string) > 100)
            $string = strip_tags($string);
            $string = html_entity_decode($string);
            $string = substr($string, 0, 240) . '...';
        return $string;
    }

    public static function getData($data)
    {
    	$date = new \DateTime($data);
    	$format = $date->format('Y-m-d\TH:i:s');
    	return $format;
    }

    public static function retornaDateTime($dia, $hora)
    {
        $dia = str_replace('/','-',$dia);
        $date = $dia.' '.$hora;
        $date = new \DateTime($date);
    	$format = $date->format('Y-m-d\TH:i:s');
    	return $format;
    }

    public static function btnSituacao($situacao)
    {
        switch ($situacao) {
            case 'Aberto':
                echo "<div class='sit-btn sit-verde'>Aberto</div>";
            break;
            
            default:
                echo "<div class='sit-btn sit-default'>".$situacao."</div>";
            break;
        }
    }
}
