<?php

namespace App\Repositories;

use App\Representante;

class RepresentanteRepository 
{
    public function getByAssId($assId)
    {
        return Representante::where("ass_id", $assId)->first();
    }

    public function getByCpfCnpj($cpfCnpj)
    {
        return Representante::where('cpf_cnpj', $cpfCnpj)->first();
    }    
}