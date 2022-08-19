<?php

namespace App\Repositories;

use App\Noticia;

class NoticiaRepository {
    // public function getToTable()
    // {
    //     return Noticia::orderBy('idnoticia','DESC')->paginate(10);
    // }

    // public function getBusca($busca)
    // {
    //     return Noticia::where('titulo','LIKE','%'.$busca.'%')
    //         ->orWhere('conteudo','LIKE','%'.$busca.'%')
    //         ->paginate(10);
    // }

    // public function getTrashed()
    // {
    //     return Noticia::onlyTrashed()->orderBy('idnoticia', 'DESC')->paginate(10);
    // }

    // public function getTrashedById($id)
    // {
    //     return Noticia::onlyTrashed()->findOrFail($id);
    // }

    // public function getSiteGrid()
    // {
    //     return Noticia::select('img','slug','titulo','created_at','conteudo')
    //         ->orderBy('created_at', 'DESC')
    //         ->where('publicada','Sim')
    //         ->paginate(9);
    // }

    // public function getBySlug($slug)
    // {
    //     return Noticia::where('slug', $slug)->firstOrFail();
    // }

    // public function getThreeExcludingOneById($id)
    // {
    //     return Noticia::latest()
    //         ->take(3)
    //         ->orderBy('created_at','DESC')
    //         ->where('idnoticia','!=',$id)
    //         ->whereNull('idregional')
    //         ->get();
    // }

    // public function getExistingSlug($slug, $id = null)
    // {
    //     if($id === null) {
    //         return Noticia::select('slug')
    //             ->where('slug',$slug)
    //             ->count();
    //     }
        
    //     return Noticia::select('slug')
    //         ->where('slug',$slug)
    //         ->where('idnoticia','!=',$id)
    //         ->count();
    // }

    // public function store($request, $slug)
    // {
    //     $publicada = noticiaPublicada();
    //     empty($request->input('categoria')) ? $categoria = null : $categoria = $request->input('categoria');
    //     return Noticia::create([
    //         'titulo' => $request->titulo,
    //         'slug' => $slug,
    //         'img' => $request->img,
    //         'conteudo' => $request->conteudo,
    //         'conteudoBusca' => converterParaTextoCru($request->conteudo),
    //         'publicada' => $publicada,
    //         'categoria' => $categoria,
    //         'idregional' => $request->idregional,
    //         'idcurso' => $request->idcurso,
    //         'idusuario' => $request->idusuario
    //     ]);
    // }

    // public function update($id, $request, $slug)
    // {
    //     $publicada = noticiaPublicada();
    //     empty($request->input('categoria')) ? $categoria = null : $categoria = $request->input('categoria');
    //     return Noticia::findOrFail($id)->update([
    //         'titulo' => $request->titulo,
    //         'slug' => $slug,
    //         'img' => $request->img,
    //         'conteudo' => $request->conteudo,
    //         'conteudoBusca' => converterParaTextoCru($request->conteudo),
    //         'publicada' => $publicada,
    //         'categoria' => $categoria,
    //         'idregional' => $request->idregional,
    //         'idcurso' => $request->idcurso,
    //         'idusuario' => $request->idusuario
    //     ]);
    // }

    // public function destroy($id)
    // {
    //     return Noticia::findOrFail($id)->delete();
    // }
}