<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Regional;
use App\Noticia;

class RegionalSiteController extends Controller
{
    public function regionaisView()
    {
        $regionais = Cache::remember('regionaisSite', 240, function(){
            return $regionais = Regional::all();
        });
        return view('site.regionais', compact('regionais'));
    }

    public function show($id)
    {
        $resultado = Regional::find($id);
    	return view('site.regional', compact('resultado'));
    }
}
