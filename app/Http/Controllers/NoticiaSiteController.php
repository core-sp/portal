<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Noticia;

class NoticiaSiteController extends Controller
{
    public function noticiasView()
    {

        $noticias = Noticia::paginate(9);
        return view('site.noticias', compact('noticias'));        
    }

    public function show($slug)
    {
        $noticia = Noticia::where('slug', $slug)->first();
        $id = $noticia->idnoticia;
        $tres = Noticia::latest()->take(3)->where('idnoticia','!=',$id)->get();
        return view('site.noticia', compact('noticia', 'tres', 'id'));
    }
}
