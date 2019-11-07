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

    public static function btnStatus($status)
    {
        switch ($status) {
            case 'Em andamento':
                echo "<span class='badge badge-success'>".$status."</span>";
            break;

            case 'Concluído':
                echo "<span class='sit-btn bg-info'>".$status."</span>";
            break;
            
            default:
                echo "<span class='sit-btn bg-secondary'>".$status."</span>";
            break;
        }
    }
}
