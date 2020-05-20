<?php

namespace App\Repositories;

use App\Concurso;
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

    public function getBuscaSite()
    {
        $buscaModalidade = IlluminateRequest::input('modalidade');
        $buscaSituacao = IlluminateRequest::input('situacao');
        $buscaNrProcesso = IlluminateRequest::input('nrprocesso');
        $dia = IlluminateRequest::input('datarealizacao');
        if(isset($dia)) {
            $diaArray = explode('/',$dia);
            $checaDia = checkdate($diaArray[1], $diaArray[0], $diaArray[2]);
            if($checaDia === false) {
                echo "<script>alert('Data inválida'); window.location.href='/concursos'</script>";
            }
            $replace = str_replace('/','-',$dia);
            $dia = new \DateTime($replace);
            $buscaDataRealizacao = $dia->format('Y-m-d');
        } else {
            $buscaDataRealizacao = '';
        }
        return Concurso::where('modalidade','LIKE','%'.$buscaModalidade.'%')
            ->where('situacao','LIKE','%'.$buscaSituacao.'%')
            ->where('nrprocesso','LIKE',$buscaNrProcesso)
            ->where('datarealizacao','LIKE','%'.$buscaDataRealizacao.'%')
            ->paginate(10);
    }
}