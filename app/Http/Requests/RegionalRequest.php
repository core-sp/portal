<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegionalRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return false;
    // }

    public function rules()
    {
        return [
            'regional' => 'required|',
            'email' => 'required|',
            'endereco' => 'required|',
            'bairro' => 'required|',
            'numero' => 'required|',
            'cep' => 'required|',
            'telefone' => 'required|',
            'funcionamento' => 'required|',
            'ageporhorario' => 'required|regex:/^[1-9]+$/',
            'descricao' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'ageporhorario.regex' => 'O valor deve ser maior que 0'
        ];
    }
}
