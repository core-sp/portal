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
}
