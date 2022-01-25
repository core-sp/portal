<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuporteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'data' => 'before_or_equal:'.date('Y-m-d', strtotime('yesterday')).'|after_or_equal:'.date('Y-m-d', strtotime('2021-08-05')),
            'texto' => 'min:3|max:191',
            'file' => 'file|mimetypes:text/plain',
        ];
    }

    public function messages()
    {
        return [
            'before_or_equal' => 'Deve selecionar uma data entre 05/08/2021 e '.date('d/m/Y', strtotime('yesterday')),
            'after_or_equal' => 'Deve selecionar uma data entre 05/08/2021 e '.date('d/m/Y', strtotime('yesterday')),
            'min' => 'O texto não pode ser menor que 3 caracteres',
            'max' => 'O texto não pode ser maior que 191 caracteres',
            'file' => 'Deve ser um arquivo válido',
            'mimetypes' => 'Somente arquivo com a extensão .txt'
        ];
    }
}
