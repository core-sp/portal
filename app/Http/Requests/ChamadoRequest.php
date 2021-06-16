<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChamadoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'tipo' => 'required',
            'prioridade' => 'required',
            'mensagem' => 'required|min:3',
            'img' => 'max:191'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'mensagem.required' => 'A mensagem é obrigatória',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'min' => 'Escreva no mínimo 3 caracteres'
        ];
    }
}
