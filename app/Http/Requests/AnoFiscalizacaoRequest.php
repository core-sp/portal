<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnoFiscalizacaoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'ano' => 'required|not_in:0|unique:anos_fiscalizacao,ano'
        ];
    }

    public function messages() 
    {
        return [
            'ano.required' => 'Por favor, informe o ano de fiscalização',
            'ano.not_in' => 'Ano inválido',
            'ano.unique' => 'Ano já está cadastrado'
        ];
    }

    public function toModel()
    {
        return [
            'ano' => $this->ano
        ];
    }
}
