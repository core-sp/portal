<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Validation\Rule;

class NoticiaRequest extends FormRequest
{
    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    public function rules()
    {
        return [
            'titulo' => [
                'required',
                'max:191',
                'min:3',
                Rule::unique('noticias', 'titulo')->ignore($this->noticia, 'idnoticia'),
            ],
            'img' => 'nullable|max:191',
            'conteudo' => 'required|min:100',
            'categoria' => 'nullable|in:'.implode(',', $this->service->getService('Noticia')->getCategorias()),
            'idregional' => 'nullable|exists:regionais',
            'idcurso' => 'nullable|exists:cursos',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'min' => 'O campo :attribute não possui o mínimo de :min caracteres obrigatório',
            'max' => 'O :attribute excedeu o limite de :max caracteres permitido',
            'unique' => 'Já existe uma notícia com este mesmo título',
            'idregional.exists' => 'Essa regional não existe',
            'idcurso.exists' => 'Esse curso não existe',
            'categoria.in' => 'Categoria inválida',
        ];
    }
}
