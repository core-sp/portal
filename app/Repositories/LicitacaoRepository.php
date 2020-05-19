<?php

namespace App\Repositories;

use App\Licitacao;
use Illuminate\Support\Facades\Request as IlluminateRequest;

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
    
    public function store($request)
    {
        $datarealizacao = retornaDateTime($request->datarealizacao, $request->horainicio);
        return Licitacao::create([
            'modalidade' => $request->modalidade,
            'uasg' => $request->uasg,
            'edital' => $request->edital,
            'titulo' => $request->titulo,
            'nrlicitacao' => $request->nrlicitacao,
            'nrprocesso' => $request->nrprocesso,
            'situacao' => $request->situacao,
            'datarealizacao' => $datarealizacao,
            'objeto' => $request->objeto,
            'idusuario' => $request->idusuario
        ]);
    }

    public function update($id, $request)
    {
        $datarealizacao = retornaDateTime($request->datarealizacao, $request->horainicio);
        return Licitacao::findOrFail($id)->update([
            'modalidade' => $request->modalidade,
            'uasg' => $request->uasg,
            'edital' => $request->edital,
            'titulo' => $request->titulo,
            'nrlicitacao' => $request->nrlicitacao,
            'nrprocesso' => $request->nrprocesso,
            'situacao' => $request->situacao,
            'datarealizacao' => $datarealizacao,
            'objeto' => $request->objeto,
            'idusuario' => $request->idusuario
        ]);
    }

    public function getSiteGrid()
    {
        return Licitacao::orderBy('created_at','DESC')->paginate(10);
    }

    public function getBuscaSite()
    {
        // Refatorar
        $buscaPalavraChave = html_entity_decode(IlluminateRequest::input('palavra-chave'));
        $buscaPalavraChave = htmlentities($buscaPalavraChave);
        $buscaModalidade = IlluminateRequest::input('modalidade');
        $buscaSituacao = IlluminateRequest::input('situacao');
        $buscaNrLicitacao = IlluminateRequest::input('nrlicitacao');
        $buscaNrProcesso = IlluminateRequest::input('nrprocesso');
        $dia = IlluminateRequest::input('datarealizacao');
        if(isset($dia)) {
            $diaArray = explode('/',$dia);
            $checaDia = checkdate($diaArray[1], $diaArray[0], $diaArray[2]);
            if($checaDia === false) {
                echo "<script>alert('Data inválida'); window.location.href='/licitacoes'</script>";
            }
            $replace = str_replace('/','-',$dia);
            $dia = new \DateTime($replace);
            $buscaDataRealizacao = $dia->format('Y-m-d');
        } else {
            $buscaDataRealizacao = '';
        }
        return Licitacao::where('objeto','LIKE','%'.$buscaPalavraChave.'%')
            ->where('modalidade','LIKE','%'.$buscaModalidade.'%')
            ->where('situacao','LIKE','%'.$buscaSituacao.'%')
            ->where('nrlicitacao','LIKE',$buscaNrLicitacao)
            ->where('nrprocesso','LIKE',$buscaNrProcesso)
            ->where('datarealizacao','LIKE','%'.$buscaDataRealizacao.'%')
            ->orderBy('created_at','DESC')
            ->paginate(10);
    }
}