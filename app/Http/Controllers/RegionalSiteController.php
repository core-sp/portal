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
        return response()
            ->view('site.regionais', compact('regionais'))
            ->header('Cache-Control','no-cache');
    }

    public function show($id)
    {
        $resultado = Regional::findOrFail($id);
        $noticias = Noticia::select('slug','img','created_at','titulo','idregional')
            ->where('idregional','=',$id)
            ->orderBy('created_at','DESC')
            ->limit(3)
            ->get();
        return response()
            ->view('site.regional', compact('resultado','noticias'))
            ->header('Cache-Control','no-cache');
    }
}
