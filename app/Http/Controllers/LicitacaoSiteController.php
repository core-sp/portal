<?php

namespace App\Http\Controllers;

use App\Licitacao;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class LicitacaoSiteController extends Controller
{
	public function licitacoesView()
    {
        $licitacoes = Licitacao::orderBy('created_at','DESC')->paginate(10);
        return response()
            ->view('site.licitacoes', compact('licitacoes'))
            ->header('Cache-Control','no-cache');
    }

    public function show($id)
    {
        $licitacao = Licitacao::findOrFail($id);
        return response()
            ->view('site.licitacao', compact('licitacao'))
            ->header('Cache-Control','no-cache');
    }

    public function buscaLicitacoes()
    {
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
        if (!empty($buscaPalavraChave)
            or !empty($buscaModalidade) 
            or !empty($buscaSituacao) 
            or !empty($buscaNrLicitacao)
            or !empty($buscaNrProcesso)
            or !empty($buscaDataRealizacao)
        ){
            $busca = true;
        } else {
            $busca = false;
        }
        $licitacoes = Licitacao::where('objeto','LIKE','%'.$buscaPalavraChave.'%')
            ->where('modalidade','LIKE','%'.$buscaModalidade.'%')
            ->where('situacao','LIKE','%'.$buscaSituacao.'%')
            ->where('nrlicitacao','LIKE',$buscaNrLicitacao)
            ->where('nrprocesso','LIKE',$buscaNrProcesso)
            ->where('datarealizacao','LIKE','%'.$buscaDataRealizacao.'%')
            ->orderBy('created_at','DESC')
            ->paginate(10);
        if (count($licitacoes) > 0) {
            return view('site.licitacoes', compact('licitacoes', 'busca'));
        } else {
            $licitacoes = null;
            return view('site.licitacoes', compact('licitacoes', 'busca'));
        }
    }
}
