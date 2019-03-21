<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Regional;
use App\Noticia;

class RegionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
    	$regionais = Regional::orderBy('regional', 'ASC')->paginate(10);
    	return view('admin.regionais.home', compact('regionais'));
    }

    public function show($id)
    {
    	$regional = Regional::find($id);
    	$noticias = Noticia::paginate(10)
            ->whereIn('idregional', [$id, null]);
    	return view('admin.regionais.mostra', compact('regional', 'noticias'));
    }

    public function busca(Request $request)
    {
        $busca = Input::get('q');
        $regionais = Regional::where('regional','LIKE','%'.$busca.'%')->paginate(10);
        if (count($regionais) > 0) 
            return view('admin.regionais.home', compact('regionais', 'busca'));
        else
            return view('admin.regionais.home')->withMessage('Nenhuma regional encontrada');
    }
}