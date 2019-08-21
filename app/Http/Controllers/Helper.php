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

    public static function resumoTamanho($string, $tamanho)
    {
        if (strlen($string) > 100)
            $string = strip_tags($string);
            $string = html_entity_decode($string);
            $string = substr($string, 0, $tamanho) . '...';
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

    public static function retornaDate($dia)
    {
        if(!empty($dia)) {
            $dia = str_replace('/','-',$dia);
            $date = new \DateTime($dia);
            $format = $date->format('Y-m-d');
            return $format;
        } else {
            $dia = null;
        }
    }

    public static function btnSituacao($situacao)
    {
        switch ($situacao) {
            case 'Aberto':
                echo "<div class='sit-btn sit-verde'>Aberto</div>";
            break;

            case 'Homologado':
                echo "<div class='sit-btn sit-verde'>Homologado</div>";
            break;

            case 'Cancelado':
                echo "<div class='sit-btn sit-vermelho'>Cancelado</div>";
            break;
            
            default:
                echo "<div class='sit-btn sit-default'>".$situacao."</div>";
            break;
        }
    }

    public static function imgToThumb($string)
    {
        $str = basename($string);
        $num = strlen($str);
        $num = $num <= 0 ? $num : -$num;
        $add = substr_replace($string,'thumbnails/'.$str,$num);
        return $add;
    }

    public static function listaCores()
    {
        return [
            1 => 'Core-AL',
            2 => 'Core-AM',
            3 => 'Core-BA',
            4 => 'Core-CE',
            5 => 'Core-DF',
            6 => 'Core-ES',
            7 => 'Core-GO',
            8 => 'Core-MA',
            9 => 'Core-MG',
            10 => 'Core-MS',
            11 => 'Core-MT',
            12 => 'Core-PA',
            13 => 'Core-PB',
            14 => 'Core-PE',
            15 => 'Core-PI',
            16 => 'Core-PR',
            17 => 'Core-RJ',
            18 => 'Core-RN',
            19 => 'Core-RO',
            20 => 'Core-RS',
            21 => 'Core-SC',
            22 => 'Core-SE',
            23 => 'Core-TO',
            24 => 'Core-SP'
        ];
    }

    public static function tipoPessoa()
    {
        return [
            2 => 'Física',
            5 => 'Física RT',
            1 => 'Jurídica'
        ];
    }
}
