<?php

namespace App\Repositories;

use App\Post;

class PostRepository {
    // public function getToTable()
    // {
    //     return Post::orderBy('id','DESC')->paginate(10);
    // }

    // public function getBySlug($slug)
    // {
    //     return Post::where('slug', $slug)->firstOrFail();
    // }

    // public function getNext($id)
    // {
    //     return Post::select('titulo', 'slug')->where('id', '>', $id)->first();
    // }

    // public function getPrevious($id)
    // {
    //     return Post::select('titulo', 'slug')->where('id', '<', $id)->orderBy('id', 'DESC')->first();
    // }

    // public function getBusca($busca)
    // {
    //     return Post::where('titulo','LIKE','%'.$busca.'%')
    //         ->orWhere('conteudo','LIKE','%'.$busca.'%')
    //         ->paginate(10);
    // }

    // public function store($data)
    // {
    //     return Post::create($data);
    // }

    // public function update($id, $data)
    // {
    //     return Post::findOrFail($id)->update($data);
    // }

    // public function delete($id)
    // {
    //     return Post::findOrFail($id)->delete();
    // }
}