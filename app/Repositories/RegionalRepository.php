<?php

namespace App\Repositories;

use App\Noticia;
use App\Regional;

class RegionalRepository {
    public function getToTable()
    {
        return Regional::all();
    }

    public function all()
    {
        return Regional::all();
    }

    public function getAsc()
    {
        return Regional::orderBy('regional', 'ASC')->get();
    }

    public function getById($id)
    {
        return Regional::findOrFail($id);
    }

    public function getBusca($busca)
    {
        return Regional::where('regional','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->paginate(10);
    }

    public function getRegionalNoticias($id)
    {
        return Noticia::select('slug','img','created_at','titulo','idregional')
            ->where('idregional','=',$id)
            ->orderBy('created_at','DESC')
            ->limit(3)
            ->get();
    }

    public function update($id, $request)
    {
        return Regional::findOrFail($id)->update($request->all());
    }
}