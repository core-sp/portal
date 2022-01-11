<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginaRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return false;
    // }

    public function rules()
    {
        return [
            'titulo' => 'required|max:191',
            'subtitulo' => 'max:191',
            'img' => 'max:191',
            'conteudo' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
    }
}
