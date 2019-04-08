<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Regional;
use App\Noticia;

class RegionalSiteController extends Controller
{
    public function regionaisView()
    {
        $regionais = Regional::all();
        return view('site.regionais', compact('regionais'));
    }

    public function show($id)
    {
    	$resultado = Regional::find($id);
    	$noticias = Noticia::paginate(10)
            ->whereIn('idregional', [$id, null]);
    	return view('site.regional', compact('resultado', 'noticias'));
    }
}
