<?php

namespace App\Repositories;

use App\Compromisso;

class CompromissoRepository 
{
    public function getAll()
    {
        return Compromisso::orderBy('data','DESC')->paginate(10);
    }

    public function store($request)
    {
        return Compromisso::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'local' => $request->local,
            'data' => $request->data,
            'horarioinicio' => onlyHour($request->horarioinicio),
            'horariotermino' => onlyHour($request->horariotermino)
        ]);
    }
}