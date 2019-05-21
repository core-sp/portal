<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Noticia;

class NoticiaSiteController extends Controller
{
    public function noticiasView()
    {
        $noticias = Cache::remember('noticiasGridSite', 60, function(){
            return Noticia::select('img','slug','titulo','created_at','conteudo')
                ->orderBy('created_at', 'DESC')
                ->where('publicada','Sim')
                ->paginate(9);
        });
        return view('site.noticias', compact('noticias'));        
    }

    public function show($slug)
    {
        $noticia = Noticia::where('slug', $slug)->first();
        if(isset($noticia)) {
            $id = $noticia->idnoticia;
            $tres = Noticia::latest()
                ->take(3)
                ->orderBy('created_at','DESC')
                ->where('idnoticia','!=',$id)
                ->whereNull('idregional')
                ->get();
            return response()
                ->view('site.noticia', compact('noticia', 'tres', 'id'))
                ->header('Cache-Control','public,max-age=900');
        } else {
            abort(404);
        }
    }
}
