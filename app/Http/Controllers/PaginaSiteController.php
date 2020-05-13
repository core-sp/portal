<?php

namespace App\Http\Controllers;

use App\Pagina;

class PaginaSiteController extends Controller
{
    public function show($slug)
    {
        $pagina = Pagina::select('titulo','slug','img','subtitulo','conteudo')
            ->where('slug', $slug)->first();
        if(isset($pagina)) {
            return response()
                ->view('site.pagina', compact('pagina'))
                ->header('Cache-Control','no-cache');
        } else {
            abort(404);
        }   
    }
}
