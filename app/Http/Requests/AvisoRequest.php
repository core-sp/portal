<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvisoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'cor_fundo_titulo' => 'required',
            'titulo' => 'required|max:191',
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
