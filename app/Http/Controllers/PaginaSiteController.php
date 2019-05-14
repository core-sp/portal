<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Pagina;

class PaginaSiteController extends Controller
{
    public function show($slug)
    {
        $pagina = Pagina::select('titulo','img','subtitulo','conteudo')
            ->where('slug', $slug)->first();
        if(isset($pagina)) {
            return view('site.pagina', compact('pagina'));
        } else {
            abort(404);
        }   
    }

    public function showCategoria($categoria, $slug)
    {
        $one = $categoria.'/'.$slug;
        $pagina = Pagina::select('titulo','img','subtitulo','conteudo')
            ->where('slug', $one)->first();
        if(isset($pagina)) {
            return view('site.pagina', compact('pagina'));
        } else {
            abort(404);
        }
    }
}
