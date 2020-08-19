<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;

class AgendamentoSiteCancelamentoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'cpf' => ['required', 'max:191', new Cpf]
        ];
    }

    public function messages() 
    {
        return [
            'cpf.required' => 'O CPF é obrigatório',
            'cpf.max' => 'O CPF excedeu o limite de caracteres permitido'
        ];
    }
}
