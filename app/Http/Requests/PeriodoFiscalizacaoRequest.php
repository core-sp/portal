<?php

namespace App\Http\Requests;

use App\PeriodoFiscalizacao;
use Illuminate\Foundation\Http\FormRequest;

class PeriodoFiscalizacaoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'periodo' => 'required_without:regional|date_format:Y|size:4|after_or_equal:2000|unique:periodos_fiscalizacao,periodo',
            'regional' => 'required|array',
            'regional.*.*' => 'required|integer|min:0|max:999999999'
        ];
    }

    public function messages() 
    {
        return [
            'required' => 'Campo obrigatório',
            'date_format' => 'Ano inválido',
            'periodo.size' => 'O ano deve conter 4 dígitos',
            'after_or_equal' => 'O ano deve ser maior ou igual a 2000',
            'unique' => 'Ano já está cadastrado',
            'min' => 'Valor deve ser maior ou igual a 0',
            'max' => 'Valor deve ser menor ou igual a 999999999',
            'integer' => 'Valor deve ser um inteiro',
            'array' => 'Campo não está formato de array no request',
        ];
    }

    // public function toModel()
    // {
    //     return [
    //         'periodo' => $this->periodo,
    //         'status' => false
    //     ];
    // }
}
