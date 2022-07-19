<?php

namespace App\Repositories;

use App\PeriodoFiscalizacao;
use App\DadoFiscalizacao;

class FiscalizacaoRepository 
{
    // public function getPublicado() 
    // {
    //     return PeriodoFiscalizacao::where('status', true)
    //         ->orderBy('periodo', 'DESC')
    //         ->paginate(25);;
    // }

    // public function getAll() 
    // {
    //     return PeriodoFiscalizacao::orderBy('periodo', 'DESC')
    //         ->paginate(25);;
    // }

    // public function busca($criterio)
    // {
    //     return PeriodoFiscalizacao::where('periodo', $criterio)
    //         ->paginate(10);
    // }

    // public function storePeriodo($data)
    // {
    //     return PeriodoFiscalizacao::create($data);
    // }

    // public function updatePeriodoStatus($id, $data)
    // {
    //     return PeriodoFiscalizacao::findOrFail($id)->update($data);
    // }

    // public function storeDadoFiscalizacao($idregional, $idperiodo)
    // {
    //     return DadoFiscalizacao::create(['idregional' => $idregional, 'idperiodo' => $idperiodo]);
    // }

    // public function updateDadoFiscalizacao($dadosFiscalizacao, $ano)
    // {
    //     $anoUpdate = PeriodoFiscalizacao::findOrFail($ano);

    //     foreach ($anoUpdate->dadoFiscalizacao as $dado) {
    //         $dado->processofiscalizacaopf = $dadosFiscalizacao[$dado->idregional]["processofiscalizacaopf"];
    //         $dado->processofiscalizacaopj = $dadosFiscalizacao[$dado->idregional]["processofiscalizacaopj"];
    //         $dado->registroconvertidopf = $dadosFiscalizacao[$dado->idregional]["registroconvertidopf"];
    //         $dado->registroconvertidopj = $dadosFiscalizacao[$dado->idregional]["registroconvertidopj"];
    //         $dado->processoverificacao = $dadosFiscalizacao[$dado->idregional]["processoverificacao"];
    //         $dado->dispensaregistro = $dadosFiscalizacao[$dado->idregional]["dispensaregistro"];
    //         $dado->notificacaort = $dadosFiscalizacao[$dado->idregional]["notificacaort"];
    //         $dado->orientacaorepresentada = $dadosFiscalizacao[$dado->idregional]["orientacaorepresentada"];
    //         $dado->orientacaorepresentante = $dadosFiscalizacao[$dado->idregional]["orientacaorepresentante"];
    //         $dado->cooperacaoinstitucional = $dadosFiscalizacao[$dado->idregional]["cooperacaoinstitucional"];

    //         $dado->update();
    //     }
    // }

    // public function findOrFail($id)
    // {
    //     return PeriodoFiscalizacao::findOrFail($id);
    // }
}