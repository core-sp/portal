<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}