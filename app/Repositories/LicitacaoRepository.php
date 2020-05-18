<?php

namespace App\Repositories;

use App\Licitacao;

class LicitacaoRepository {
    public function getToTable()
    {
        return Licitacao::orderBy('idlicitacao','DESC')->paginate(10);
    }

    public function findById($id)
    {
        return Licitacao::findOrFail($id);
    }

    public function getTrashed()
    {
        return Licitacao::onlyTrashed()->paginate(10);
    }

    public function getTrashedById($id)
    {
        return Licitacao::onlyTrashed()->findOrFail($id);
    }

    public function getBusca($busca)
    {
        return Licitacao::where('modalidade','LIKE','%'.$busca.'%')
            ->orWhere('nrlicitacao','LIKE','%'.$busca.'%')
            ->orWhere('nrprocesso','LIKE','%'.$busca.'%')
            ->orWhere('situacao','LIKE','%'.$busca.'%')
            ->orWhere('objeto','LIKE','%'.$busca.'%')
            ->paginate(10);
    }
}