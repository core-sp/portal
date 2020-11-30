<?php

namespace App\Repositories;

use App\PeriodoFiscalizacao;
use App\DadoFiscalizacao;

class FiscalizacaoRepository 
{
    public function getPublicado() 
    {
        return PeriodoFiscalizacao::where('status', true)
            ->orderBy('ano', 'DESC')
            ->paginate(25);;
    }

    public function busca($criterio)
    {
        return PeriodoFiscalizacao::where('ano', $criterio)
            ->paginate(10);
    }

    public function storeAno($data)
    {
        return PeriodoFiscalizacao::create($data);
    }

    public function updateAnoStatus($ano, $data)
    {
        return PeriodoFiscalizacao::findOrFail($ano)->update($data);
    }

    public function storeDadoFiscalizacao($idregional, $ano)
    {
        return DadoFiscalizacao::create(['idregional' => $idregional, 'ano' => $ano]);
    }

    public function updateDadoFiscalizacao($dadosFiscalizacao, $ano)
    {
        $anoUpdate = PeriodoFiscalizacao::findOrFail($ano);

        foreach ($anoUpdate->dadoFiscalizacao as $dado) {
            $dado->notificacaopf = $dadosFiscalizacao[$dado->idregional]["notificacaopf"];
            $dado->notificacaopj = $dadosFiscalizacao[$dado->idregional]["notificacaopj"];
            $dado->constatacaopf = $dadosFiscalizacao[$dado->idregional]["constatacaopf"];
            $dado->constatacaopj = $dadosFiscalizacao[$dado->idregional]["constatacaopj"];
            $dado->infracaopf = $dadosFiscalizacao[$dado->idregional]["infracaopf"];
            $dado->infracaopj = $dadosFiscalizacao[$dado->idregional]["infracaopj"];
            $dado->convertidopf = $dadosFiscalizacao[$dado->idregional]["convertidopf"];
            $dado->convertidopj = $dadosFiscalizacao[$dado->idregional]["convertidopj"];
            $dado->orientacao = $dadosFiscalizacao[$dado->idregional]["orientacao"];

            $dado->update();
        }
    }

    public function findOrFail($ano)
    {
        return PeriodoFiscalizacao::findOrFail($ano);
    }
    
    public function getAll() 
    {
        return PeriodoFiscalizacao::orderBy('ano', 'DESC')->get();
    }
}