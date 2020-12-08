<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use App\Rules\Cnpj;
use Illuminate\Foundation\Http\FormRequest;

class PreCadastroRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|max:191',
            'cpf' => ['required', 'max:191', new Cpf],
            'cnpj' => ['required', 'max:191', new Cnpj],
            'email' => 'required|email|max:191',
            'anexo' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Campo obrigatório',
            'max' => 'Excedido limite de caracteres',
            'mimes' => 'Tipo de arquivo não suportado',
            'max' => 'Arquivo não pode ultrapassar 2MB'
        ];
    }
}
