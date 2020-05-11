<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Noticia;
use App\Repositories\NoticiaRepository;

class NoticiaSiteController extends Controller
{
    public function noticiasView()
    {
        $noticias = (new NoticiaRepository())->getSiteGrid();
        return view('site.noticias', compact('noticias'));
    }

    public function show($slug)
    {
        $noticia = (new NoticiaRepository())->getBySlug($slug);
        $titulo = $noticia->titulo;
        $id = $noticia->idnoticia;
        $tres = (new NoticiaRepository())->getThreeExcludingOneById($id);
        return response()
            ->view('site.noticia', compact('noticia', 'titulo', 'tres', 'id'))
            ->header('Cache-Control','no-cache');
    }
}
