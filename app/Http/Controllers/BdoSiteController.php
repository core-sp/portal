<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoOportunidade;

class BdoSiteController extends Controller
{
    public function index()
    {
        $oportunidades = BdoOportunidade::orderBy('created_at','DESC')->paginate(10);
        return view('site.balcao-de-oportunidades', compact('oportunidades'));
    }

    public function buscaOportunidades()
    {
    	$buscaPalavraChave = Input::get('palavra-chave');
        $buscaSegmento = Input::get('segmento');
        $buscaRegional = ','.Input::get('regional').',';
        if(Input::get('regional') === 'todas')
            $buscaRegional = '';
        if (!empty($buscaPalavraChave) 
            or !empty($buscaSegmento) 
        ){
            $busca = true;
        } else {
            $busca = false;
        }
        $oportunidades = BdoOportunidade::where('segmento','LIKE',$buscaSegmento)
            ->where('regiaoatuacao','LIKE','%'.$buscaRegional.'%')
            ->where(function($query) use ($buscaPalavraChave){
                $query->where('descricao','LIKE','%'.$buscaPalavraChave.'%')
                    ->orWhere('titulo','LIKE','%'.$buscaPalavraChave.'%');
            })->orderBy('created_at','DESC')
            ->paginate(10);
        if (count($oportunidades) > 0) {
            return view('site.balcao-de-oportunidades', compact('oportunidades', 'busca'));
        } else {
            $oportunidades = null;
            return view('site.balcao-de-oportunidades', compact('oportunidades', 'busca'));
        }
    }
}
