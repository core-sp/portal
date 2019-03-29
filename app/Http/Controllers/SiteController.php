<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Noticia;
use App\Licitacao;

class SiteController extends Controller
{
    public function index()
    {	
    	$noticias = Noticia::limit(3)->get();
    	return view('site.home', compact('noticias'));
    }

    public function licitacoesView()
    {
    	return view('site.licitacoes');
    } 
}
