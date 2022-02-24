<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TermoConsentimentoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email|max:191'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute é obrigatório',
            'max' => 'Excedido limite de :max caracteres',
            'email' => 'Email no formato inválido'
        ];
    }
}
