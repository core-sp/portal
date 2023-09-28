<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TermoConsentimentoRequest extends FormRequest
{
    public function rules()
    {
        if(\Route::is('termo.consentimento.upload'))
            return [
                'file' => 'required|mimes:pdf|max:2048'
            ];
        return [
            'email' => 'required|email|max:191'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute é obrigatório',
            'max' => 'Excedido limite de :max caracteres',
            'email' => 'Email no formato inválido',
            'mimes' => 'Tipo de arquivo não suportado',
            'file.max' => 'Limite de até 2MB o tamanho do arquivo'
        ];
    }
}
