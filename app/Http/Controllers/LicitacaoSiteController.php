<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Licitacao;
use Redirect;

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
        $licitacao = Licitacao::find($id);
        return response()
            ->view('site.licitacao', compact('licitacao'))
            ->header('Cache-Control','no-cache');
    }

    public function buscaLicitacoes()
    {
        $buscaPalavraChave = html_entity_decode(Input::get('palavra-chave'));
        $buscaPalavraChave = htmlentities($buscaPalavraChave);
        $buscaModalidade = Input::get('modalidade');
        $buscaSituacao = Input::get('situacao');
        $buscaNrLicitacao = Input::get('nrlicitacao');
        $buscaNrProcesso = Input::get('nrprocesso');
        $dia = Input::get('datarealizacao');
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
            ->paginate(10);
        if (count($licitacoes) > 0) {
            return view('site.licitacoes', compact('licitacoes', 'busca'));
        } else {
            $licitacoes = null;
            return view('site.licitacoes', compact('licitacoes', 'busca'));
        }
    }
}
