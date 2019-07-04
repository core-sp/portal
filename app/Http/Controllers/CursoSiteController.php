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
        $now = date('Y-m-d H:i:s');
        $cursos = Curso::select('idcurso','img','idregional','tipo','tema','resumo')
                       ->where('datatermino','>=',$now)
                       ->where('publicado','Sim')
                       ->paginate(10);
        return response()
            ->view('site.cursos', compact('cursos'))
            ->header('Cache-Control','no-cache');
    }

    public function cursosAnterioresView()
    {
        $now = date('Y-m-d H:i:s');
        $cursos = Curso::where('datatermino','<',$now)
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
        $curso = Curso::select('datatermino')
            ->findOrFail($id);
        $now = date('Y-m-d H:i:s');
        if($curso->datatermino >= $now)
            return true;
        else
            return false;
    }

    public function cursoView($id)
    {
        $curso = Curso::findOrFail($id);
        return response()
            ->view('site.curso', compact('curso'))
            ->header('Cache-Control','no-cache');
    }
}
