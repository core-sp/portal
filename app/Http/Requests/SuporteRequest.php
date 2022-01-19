<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuporteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'data' => 'before_or_equal:'.date('Y-m-d', strtotime('yesterday')).'|after_or_equal:'.date('Y-m-d', strtotime('2019-06-01')),
            'texto' => 'min:3|max:191',
        ];
    }

    public function messages()
    {
        return [
            'before_or_equal' => 'Deve selecionar uma data entre 01/06/2019 e '.date('d/m/Y', strtotime('yesterday')),
            'after_or_equal' => 'Deve selecionar uma data entre '.date('d/m/Y', strtotime('yesterday')).' e 01/06/2019',
            'min' => 'O texto não pode ser menor que 3 caracteres',
            'max' => 'O texto não pode ser maior que 191 caracteres',
        ];
    }
}
