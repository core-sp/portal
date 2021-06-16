<?php

namespace App\Repositories;

use App\Concurso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class ConcursoRepository {
    public function getToTable()
    {
        return Concurso::orderBy('idconcurso','DESC')->paginate(10);
    }

    public function getById($id)
    {
        return Concurso::findOrFail($id);
    }

    public function store($request)
    {
        $datarealizacao = retornaDateTime($request->datarealizacao, $request->horainicio);

        return Concurso::create([
            'modalidade' => $request->modalidade,
            'titulo' => $request->titulo,
            'nrprocesso' => $request->nrprocesso,
            'situacao' => $request->situacao,
            'datarealizacao' => $datarealizacao,
            'objeto' => $request->objeto,
            'linkexterno' => $request->linkexterno,
            'idusuario' => $request->idusuario
        ]);
    }

    public function update($id, $request)
    {
        $datarealizacao = retornaDateTime($request->datarealizacao, $request->horainicio);

        return Concurso::findOrFail($id)->update([
            'modalidade' => $request->modalidade,
            'titulo' => $request->titulo,
            'nrprocesso' => $request->nrprocesso,
            'situacao' => $request->situacao,
            'datarealizacao' => $datarealizacao,
            'objeto' => $request->objeto,
            'linkexterno' => $request->linkexterno,
            'idusuario' => $request->idusuario
        ]);
    }

    public function destroy($id)
    {
        return Concurso::findOrFail($id)->delete();
    }

    public function getTrashed()
    {
        return Concurso::onlyTrashed()->paginate(10);
    }

    public function restore($id)
    {
        return Concurso::onlyTrashed()->findOrFail($id)->restore();
    }

    public function getBusca($busca)
    {
        return Concurso::where('modalidade','LIKE','%'.$busca.'%')
            ->orWhere('nrprocesso','LIKE','%'.$busca.'%')
            ->orWhere('situacao','LIKE','%'.$busca.'%')
            ->orWhere('objeto','LIKE','%'.$busca.'%')
            ->paginate(10);
    }

    public function siteGrid()
    {
        return Concurso::orderBy('datarealizacao','DESC')->paginate(10);
    }

    public function getBuscaSite($buscaModalidade, $buscaSituacao, $buscaNrProcesso, $buscaDia)
    {
        $concursos = DB::table('concursos');

        if(!empty($buscaModalidade)) {
            $concursos->where("modalidade", $buscaModalidade);
        }

        if(!empty($buscaSituacao)) {
            $concursos->where("situacao", $buscaSituacao);
        }

        if(!empty($buscaNrProcesso)) {
            $concursos->where("nrprocesso", "LIKE", "%" . $buscaNrProcesso . "%");
        }

        if(!empty($buscaDia)) {
            $concursos->whereDate("datarealizacao", $buscaDia);
        }

        return $concursos->orderBy("datarealizacao", "DESC")->paginate(10);
    }
}