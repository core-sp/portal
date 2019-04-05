<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Concurso;

class ConcursoSiteController extends Controller
{
    public function show($id)
    {
        $concurso = Concurso::find($id);
        return view('site.concurso', compact('concurso'));
    }

    public function concursosView()
    {
        $concursos = Concurso::paginate(9);
        return view('site.concursos', compact('concursos'));
    }

    public function buscaConcursos()
    {
        $buscaModalidade = Input::get('modalidade');
        $buscaSituacao = Input::get('situacao');
        $buscaNrProcesso = Input::get('nrprocesso');
        $buscaDataRealizacao = Input::get('datarealizacao');
        if (!empty($buscaModalidade) 
            or !empty($buscaSituacao)
            or !empty($buscaNrProcesso)
            or !empty($buscaDataRealizacao)
        ){
            $busca = true;
        } else {
            $busca = false;
        }
        $concursos = Concurso::where('modalidade','LIKE','%'.$buscaModalidade.'%')
            ->where('situacao','LIKE','%'.$buscaSituacao.'%')
            ->where('nrprocesso','LIKE',$buscaNrProcesso)
            ->where('datarealizacao','LIKE',$buscaDataRealizacao)
            ->paginate(10);
        if (count($concursos) > 0) {
            return view('site.concursos', compact('concursos', 'busca'));
        } else {
            $concursos = null;
            return view('site.concursos', compact('concursos', 'busca'));
        }
    }
}
