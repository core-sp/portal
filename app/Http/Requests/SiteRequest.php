<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SiteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'arquivo_lfm' => 'required|max:191|starts_with:/arquivos/',
        ];
    }

    public function messages()
    {
        return [
            'max' => 'O texto não pode ser maior que 191 caracteres',
            'required' => 'Caminho obrigatório',
            'starts_with' => 'Caminho inválido',
        ];
    }
}
