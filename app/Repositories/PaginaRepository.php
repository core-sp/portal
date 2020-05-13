<?php

namespace App\Repositories;

use App\Pagina;

class PaginaRepository {
    public function getToTable()
    {
        return Pagina::orderBy('idpagina','DESC')->paginate(10);
    }

    public function countBySlug($slug)
    {
        return Pagina::select('slug')->where('slug',$slug)->count();
    }

    public function findById($id)
    {
        return Pagina::findOrFail($id);
    }

    public function getTrashed()
    {
        return Pagina::onlyTrashed()->paginate(10);
    }

    public function getTrashedById($id)
    {
        return Pagina::onlyTrashed()->findOrFail($id);
    }

    public function getBusca($busca)
    {
        return Pagina::where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->paginate(10);
    }
}