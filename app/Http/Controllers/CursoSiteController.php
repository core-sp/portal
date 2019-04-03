<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Curso;
use App\CursoInscritoController;

class CursoSiteController extends Controller
{
    public function cursosView()
    {
        $now = now();
        $cursos = Curso::where('datarealizacao','>',$now)->paginate(10);
        return view('site.cursos', compact('cursos'));
    }

    public function cursoView($id)
    {
        $curso = Curso::find($id);
        return view('site.curso', compact('curso'));
    }
}
