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

    public function getById($id)
    {
        return Curso::findOrFail($id);
    }

    public function getTrashedById($id)
    {
        return Curso::onlyTrashed()->findOrFail($id);
    }

    public function getTrashed()
    {
        return Curso::onlyTrashed()->paginate(10);
    }

    public function getSiteGrid()
    {
        $now = date('Y-m-d H:i:s');
        return Curso::select('idcurso','img','idregional','tipo','tema','resumo', 'datarealizacao')
            ->where('datatermino','>=',$now)
            ->where('publicado','Sim')
            ->paginate(9);
    }

    public function store($request)
    {
        $datarealizacao = retornaDateTime($request->datarealizacao, $request->horainicio);
        $datatermino = retornaDateTime($request->datatermino, $request->horatermino);

        return Curso::create([
            'tipo' => $request->tipo,
            'tema' => $request->tema,
            'datarealizacao' => $datarealizacao,
            'datatermino' => $datatermino,
            'endereco' => $request->endereco,
            'img' => $request->img,
            'nrvagas' => $request->nrvagas,
            'idregional' => $request->idregional,
            'descricao' => $request->descricao,
            'publicado' => $request->publicado,
            'resumo' => $request->resumo,
            'idusuario' => $request->idusuario
        ]);
    }

    public function update($id, $request)
    {
        $datarealizacao = retornaDateTime($request->datarealizacao, $request->horainicio);
        $datatermino = retornaDateTime($request->datatermino, $request->horatermino);

        return Curso::findOrFail($id)->update([
            'tipo' => $request->tipo,
            'tema' => $request->tema,
            'datarealizacao' => $datarealizacao,
            'datatermino' => $datatermino,
            'endereco' => $request->endereco,
            'img' => $request->img,
            'nrvagas' => $request->nrvagas,
            'idregional' => $request->idregional,
            'descricao' => $request->descricao,
            'publicado' => $request->publicado,
            'resumo' => $request->resumo,
            'idusuario' => $request->idusuario
        ]);
    }

    public function getTotalInscritos()
    {
        return CursoInscrito::all()->count();
    }
}