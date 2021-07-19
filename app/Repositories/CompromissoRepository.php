<?php

namespace App\Repositories;

use App\Compromisso;

class CompromissoRepository 
{
    public function getAll()
    {
        return Compromisso::orderBy('data','DESC')->paginate(10);
    }

    public function getById($id)
    {
        return Compromisso::findOrFail($id);
    }

    public function getByData($data)
    {
        return Compromisso::where('data', $data)
            ->orderBy('horarioinicio','ASC')
            ->paginate(10);

    }

    public function getBusca($busca)
    {
        return Compromisso::where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('descricao','LIKE','%'.$busca.'%')
            ->orWhere('local','LIKE','%'.$busca.'%')
            ->paginate(10);
    }

    public function store($request)
    {
        return Compromisso::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'local' => $request->local,
            'data' => retornaDate($request->data),
            'horarioinicio' => onlyHour($request->horarioinicio),
            'horariotermino' => onlyHour($request->horariotermino)
        ]);
    }

    public function update($id, $request)
    {
        return Compromisso::findOrFail($id)->update([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'local' => $request->local,
            'data' => retornaDate($request->data),
            'horarioinicio' => onlyHour($request->horarioinicio),
            'horariotermino' => onlyHour($request->horariotermino)
        ]);
    }

    public function deleteBy($id)
    {
        return Compromisso::findOrFail($id)->delete();
    }
}