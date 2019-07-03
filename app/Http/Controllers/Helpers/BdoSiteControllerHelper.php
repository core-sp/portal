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
                echo "<div class='sit-btn bg-success'>".$status."</div>";
            break;

            case 'Concluído':
                echo "<div class='sit-btn bg-info'>".$status."</div>";
            break;
            
            default:
                echo "<div class='sit-btn bg-secondary'>".$status."</div>";
            break;
        }
    }
}
