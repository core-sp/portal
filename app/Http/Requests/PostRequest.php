<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'titulo' => 'required|max:191|unique:posts,titulo,'. $this->post,
            'subtitulo' => 'required|max:191',
            'img' => 'required',
            'conteudo' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Este campo é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'unique' => 'Já existe um post com este mesmo título'
        ];
    }

    public function toModel()
    {
        return [
            'titulo' => $this->titulo,
            'subtitulo' => $this->subtitulo,
            'slug' => str_slug($this->titulo),
            'img' => $this->img,
            'conteudo' => $this->conteudo,
            'conteudoBusca' => converterParaTextoCru($this->conteudo),
            'idusuario' => $this->idusuario
        ];
    }
}
