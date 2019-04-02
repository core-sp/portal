<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Curso;

class CursoSiteController extends Controller
{
    public function cursosView()
    {
        $now = now();
        $cursos = Curso::where('datarealizacao','>',$now)->paginate(10);
        return view('site.cursos', compact('cursos'));
    }
}
