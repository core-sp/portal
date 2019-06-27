<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Regional;

class BdoSiteControllerHelper extends Controller
{
    public static function regionais()
    {
        $regionais = Regional::select('idregional','regional')->get();
        return $regionais;
    }
}
