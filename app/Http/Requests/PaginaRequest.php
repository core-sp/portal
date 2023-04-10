<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaginaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'titulo' => [
                'required',
                'max:191',
                'min:3',
                Rule::unique('paginas', 'titulo')->ignore($this->pagina, 'idpagina'),
            ],
            'subtitulo' => 'max:191',
            'img' => 'max:191',
            'conteudo' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'min' => 'O :attribute deve ter pelo menos 3 caracteres',
            'unique' => 'Já existe uma página com este mesmo título',
        ];
    }
}
