<?php

namespace App\Repositories;

use App\Pagina;

class PaginaRepository {
    public function getToTable()
    {
        return Pagina::orderBy('idpagina','DESC')->paginate(10);
    }

    public function countBySlug($slug, $id = null)
    {
        if($id === null) {
            return Pagina::select('slug')->where('slug',$slug)->count();
        }

        return Pagina::select('slug')
            ->where('idpagina', '!=', $id)
            ->where('slug',$slug)
            ->count();
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

    public function store($request, $slug)
    {
        return Pagina::create([
            'titulo' => $request->titulo,
            'subtitulo' => $request->subtitulo,
            'slug' => $slug,
            'img' => $request->img,
            'conteudo' => $request->conteudo,
            'conteudoBusca' => converterParaTextoCru($request->conteudo),
            'idusuario' => $request->idusuario
        ]);
    }

    public function update($id, $request, $slug)
    {
        return Pagina::findOrFail($id)->update([
            'titulo' => $request->titulo,
            'subtitulo' => $request->subtitulo,
            'slug' => $slug,
            'img' => $request->img,
            'conteudo' => $request->conteudo,
            'conteudoBusca' => converterParaTextoCru($request->conteudo),
            'idusuario' => $request->idusuario
        ]);
    }

    public function show($slug)
    {
        return Pagina::select('titulo','slug','img','subtitulo','conteudo')
            ->where('slug', $slug)
            ->first();
    }
}