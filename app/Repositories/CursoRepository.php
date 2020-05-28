<?php

namespace App\Repositories;

use App\Curso;
use App\CursoInscrito;

class CursoRepository {
    public function getToTable()
    {
        return Curso::orderBy('idcurso','DESC')->paginate(10);
    }

    public function getCursoContagem($id)
    {
        return CursoInscrito::where('idcurso', $id)->count();
    }
}