<?php

namespace App\Http\Requests;

use App\Rules\RecaptchaRequired;
use Illuminate\Foundation\Http\FormRequest;

class CompromissoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'data' => 'required|date_format:d/m/Y',
            'horarioinicio' => 'required|date_format:H:i',
            'horariotermino' => 'required|date_format:H:i',
            'local' => 'required|max:191',
            'titulo' => 'required|max:191',
            'descricao' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Campo é obrigatório',
            'date_format' => 'Formato inválido',
            'max' => 'O campo excedeu o limite de caracteres permitido'
        ];
    }
}
