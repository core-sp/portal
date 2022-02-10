<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class SolicitaCedulaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nome' => 'sometimes|required|min:6|max:191',
            'rg' => 'sometimes|required|max:15',
            'cpf' => [
                'sometimes', 
                'required', 
                new CpfCnpj
            ],
            "cep" => "sometimes|required",
            "bairro" => "sometimes|required|max:100",
            "logradouro" => "sometimes|required|max:100",
            "numero" => "sometimes|required|max:15",
            "complemento" => "max:100",
            "estado" => "sometimes|required|max:5",
            "municipio" => "sometimes|required|max:100",
            'justificativa' => 'sometimes|required|min:5|max:600'
        ];
    }

    public function messages()
    {
        return [
            "required" => "Campo obrigatório",
            "justificativa.max" => "Excedido limite de 600 caracteres",
            "max" => "Excedido limite de caracteres",
            'min' => 'O :attribute deve ter, no mínimo, 5 caracteres',
        ];
    }
}
