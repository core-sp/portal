<?php

namespace App\Repositories;

use App\AnoFiscalizacao;
use App\DadoFiscalizacao;

class FiscalizacaoRepository 
{
    public function getToTable() 
    {
        return AnoFiscalizacao::orderBy('ano', 'DESC')->paginate(25);;
    }

    public function storeAno($data)
    {
        return AnoFiscalizacao::create($data);
    }

    public function updateAno($ano, $data)
    {
        return AnoFiscalizacao::findOrFail($ano)->update($data);
    }

    public function storeDadoFiscalizacao($idregional, $ano)
    {
        return DadoFiscalizacao::create(['idregional' => $idregional, 'ano' => $ano]);
    }

    public function findOrFail($ano)
    {
        return AnoFiscalizacao::findOrFail($ano);
    }
    
    public function getAll() 
    {
        return AnoFiscalizacao::orderBy('ano', 'DESC')->get();
    }
}