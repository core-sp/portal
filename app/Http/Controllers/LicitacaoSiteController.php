<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Licitacao;

class LicitacaoSiteController extends Controller
{
	public function licitacoesView()
    {
        $licitacoes = Licitacao::orderBy('created_at','DESC')->paginate(10);
    	return view('site.licitacoes', compact('licitacoes'));
    }

    public function show($id)
    {
        $licitacao = Licitacao::find($id);
        return view('site.licitacao', compact('licitacao'));
    }

    public function buscaLicitacoes()
    {
        $buscaModalidade = Input::get('modalidade');
        $buscaSituacao = Input::get('situacao');
        $buscaNrLicitacao = Input::get('nrlicitacao');
        $buscaNrProcesso = Input::get('nrprocesso');
        $buscaDataRealizacao = Input::get('datarealizacao');
        if (!empty($buscaModalidade) 
            or !empty($buscaSituacao) 
            or !empty($buscaNrLicitacao)
            or !empty($buscaNrProcesso)
            or !empty($buscaDataRealizacao)
        ){
            $busca = true;
        } else {
            $busca = false;
        }
        $licitacoes = Licitacao::where('modalidade','LIKE','%'.$buscaModalidade.'%')
            ->where('situacao','LIKE','%'.$buscaSituacao.'%')
            ->where('nrlicitacao','LIKE',$buscaNrLicitacao)
            ->where('nrprocesso','LIKE',$buscaNrProcesso)
            ->where('datarealizacao','LIKE',$buscaDataRealizacao)
            ->paginate(10);
        if (count($licitacoes) > 0) {
            return view('site.licitacoes', compact('licitacoes', 'busca'));
        } else {
            $licitacoes = null;
            return view('site.licitacoes', compact('licitacoes', 'busca'));
        }
    }
}
