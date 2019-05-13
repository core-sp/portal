<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Curso;
use App\Noticia;
use App\CursoInscritoController;

class CursoSiteController extends Controller
{
    public function cursosView()
    {
        $now = now();
        $cursos = Cache::remember('cursosSite', 240, function(){
            return Curso::select('idcurso','img','idregional','tipo','tema','datarealizacao','resumo')
                ->where('datarealizacao','>=',$now)
                ->where('publicado','Sim')
                ->paginate(10);
        });
        return view('site.cursos', compact('cursos'));
    }

    public function cursosAnterioresView()
    {
        $now = now();
        $cursos = Curso::where('datarealizacao','<',$now)
            ->where('publicado','Sim')
            ->paginate(10);
        return view('site.cursos-anteriores', compact('cursos'));
    }

    public static function getNoticia($id)
    {
        $noticia = Noticia::where('idcurso',$id)->first();
        if(isset($noticia))
            return $noticia->slug;
    }

    public static function checkCurso($id)
    {
        $curso = Curso::select('datarealizacao')->find($id);
        $now = now();
        if($curso->datarealizacao > $now)
            return true;
        else
            return false;
    }

    public function cursoView($id)
    {
        $curso = Curso::find($id);
        return view('site.curso', compact('curso'));
    }
}
