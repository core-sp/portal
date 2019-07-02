<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Curso;
use App\Noticia;
use App\CursoInscritoController;

class CursoSiteController extends Controller
{
    public function cursosView()
    {
        $now = now();
        $cursos = Curso::select('idcurso','img','idregional','tipo','tema','datarealizacao','resumo')
                       ->where('datarealizacao','>=',$now)
                       ->where('publicado','Sim')
                       ->paginate(10);
        return response()
            ->view('site.cursos', compact('cursos'))
            ->header('Cache-Control','no-cache');
    }

    public function cursosAnterioresView()
    {
        $now = now();
        $cursos = Curso::where('datarealizacao','<',$now)
            ->where('publicado','Sim')
            ->paginate(10);
        return response()
            ->view('site.cursos-anteriores', compact('cursos'))
            ->header('Cache-Control','no-cache');
    }

    public static function getNoticia($id)
    {
        $noticia = Noticia::where('idcurso',$id)->first();
        if(isset($noticia))
            return $noticia->slug;
    }

    public static function checkCurso($id)
    {
        $curso = Curso::select('datarealizacao')
            ->find($id);
        $now = now();
        if($curso->datarealizacao > $now)
            return true;
        else
            return false;
    }

    public function cursoView($id)
    {
        $curso = Curso::find($id);
        return response()
            ->view('site.curso', compact('curso'))
            ->header('Cache-Control','no-cache');
    }
}
