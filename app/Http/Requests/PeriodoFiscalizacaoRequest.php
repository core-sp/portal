<?php

namespace App\Http\Requests;

use App\PeriodoFiscalizacao;
use Illuminate\Foundation\Http\FormRequest;

class PeriodoFiscalizacaoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'periodo' => 'required|not_in:0|unique:periodos_fiscalizacao,periodo'
        ];
    }

    public function messages() 
    {
        return [
            'periodo.required' => 'Por favor, informe o ano de fiscalização',
            'periodo.not_in' => 'Ano inválido',
            'periodo.unique' => 'Ano já está cadastrado'
        ];
    }

    public function toModel()
    {
        return [
            'periodo' => $this->periodo,
            'status' => false
        ];
    }
}
