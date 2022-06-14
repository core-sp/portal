<?php

namespace App\Repositories;

use App\Licitacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class LicitacaoRepository {
    // public function getToTable()
    // {
    //     return Licitacao::orderBy('idlicitacao','DESC')->paginate(10);
    // }

    // public function findById($id)
    // {
    //     return Licitacao::findOrFail($id);
    // }

    // public function getTrashed()
    // {
    //     return Licitacao::onlyTrashed()->paginate(10);
    // }

    // public function getTrashedById($id)
    // {
    //     return Licitacao::onlyTrashed()->findOrFail($id);
    // }

    // public function getBusca($busca)
    // {
    //     return Licitacao::where('modalidade','LIKE','%'.$busca.'%')
    //         ->orWhere('nrlicitacao','LIKE','%'.$busca.'%')
    //         ->orWhere('nrprocesso','LIKE','%'.$busca.'%')
    //         ->orWhere('situacao','LIKE','%'.$busca.'%')
    //         ->orWhere('objeto','LIKE','%'.$busca.'%')
    //         ->paginate(10);
    // }
    
    // public function store($request)
    // {
    //     $datarealizacao = retornaDateTime($request->datarealizacao, $request->horainicio);
    //     return Licitacao::create([
    //         'modalidade' => $request->modalidade,
    //         'uasg' => $request->uasg,
    //         'edital' => $request->edital,
    //         'titulo' => $request->titulo,
    //         'nrlicitacao' => $request->nrlicitacao,
    //         'nrprocesso' => $request->nrprocesso,
    //         'situacao' => $request->situacao,
    //         'datarealizacao' => $datarealizacao,
    //         'objeto' => $request->objeto,
    //         'idusuario' => $request->idusuario
    //     ]);
    // }

    // public function update($id, $request)
    // {
    //     $datarealizacao = retornaDateTime($request->datarealizacao, $request->horainicio);
    //     return Licitacao::findOrFail($id)->update([
    //         'modalidade' => $request->modalidade,
    //         'uasg' => $request->uasg,
    //         'edital' => $request->edital,
    //         'titulo' => $request->titulo,
    //         'nrlicitacao' => $request->nrlicitacao,
    //         'nrprocesso' => $request->nrprocesso,
    //         'situacao' => $request->situacao,
    //         'datarealizacao' => $datarealizacao,
    //         'objeto' => $request->objeto,
    //         'idusuario' => $request->idusuario
    //     ]);
    // }

    // public function getSiteGrid()
    // {
    //     return Licitacao::orderBy('created_at','DESC')->paginate(10);
    // }

    // public function getBuscaSite($buscaPalavraChave, $buscaModalidade, $buscaSituacao, $buscaNrLicitacao, $buscaNrProcesso, $buscaDia)
    // {
    //     $licitacoes = DB::table('licitacoes');

    //     if(!empty($buscaPalavraChave)) {
    //         $licitacoes->where('objeto', 'LIKE', '%'.$buscaPalavraChave.'%');
    //     }

    //     if(!empty($buscaModalidade)) {
    //         $licitacoes->where('modalidade', $buscaModalidade);
    //     }

    //     if(!empty($buscaSituacao)) {
    //         $licitacoes->where('situacao', $buscaSituacao);
    //     }

    //     if(!empty($buscaNrLicitacao)) {
    //         $licitacoes->where('nrlicitacao', 'LIKE', '%'.$buscaNrLicitacao.'%');
    //     }

    //     if(!empty($buscaNrProcesso)) {
    //         $licitacoes->where('nrprocesso', 'LIKE', '%'.$buscaNrProcesso.'%');
    //     }

    //     if(!empty($buscaDia)) {
    //         $licitacoes->whereDate('datarealizacao', $buscaDia);
    //     }

    //     return $licitacoes->orderBy('datarealizacao', 'DESC')->paginate(10);
    // }
}