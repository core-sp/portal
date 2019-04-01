<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoOportunidade;

class BdoSiteController extends Controller
{
    public function index()
    {
    	$oportunidades = BdoOportunidade::paginate(10);
    	return view('site.balcao-de-oportunidades', compact('oportunidades'));
    }

    public function buscaOportunidades()
    {
    	$buscaPalavraChave = Input::get('palavrachave');
        $buscaSegmento = Input::get('segmento');
        if (!empty($buscaPalavraChave) 
            or !empty($buscaSegmento) 
        ){
            $busca = true;
        } else {
            $busca = false;
        }
        $oportunidades = BdoOportunidade::where('segmento','LIKE',$buscaSegmento)
            ->where('descricao','LIKE','%'.$buscaPalavraChave.'%')
            ->paginate(10);
        if (count($oportunidades) > 0) {
            return view('site.balcao-de-oportunidades', compact('oportunidades', 'busca'));
        } else {
            $oportunidades = null;
            return view('site.balcao-de-oportunidades', compact('oportunidades', 'busca'));
        }
    }

    public function show($id)
    {
    	$oportunidade = BdoOportunidade::find($id);
    	return view('site.oportunidade', compact('oportunidade'));
    }
}
