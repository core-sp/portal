<?php

namespace App\Repositories;

use App\Representante;

class RepresentanteRepository 
{
    public function getByAssId($assId)
    {
        return Representante::where("ass_id", $assId)->first();
    }
}