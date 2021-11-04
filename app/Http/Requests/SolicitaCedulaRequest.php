<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitaCedulaRequest extends FormRequest
{
    public function rules()
    {
        return [
            "cep" => "sometimes|required",
            "bairro" => "sometimes|required|max:100",
            "logradouro" => "sometimes|required|max:100",
            "numero" => "sometimes|required|max:15",
            "complemento" => "max:100",
            "estado" => "sometimes|required|max:5",
            "municipio" => "sometimes|required|max:100",
            'justificativa' => 'sometimes|required|min:5|max:191'
        ];
    }

    public function messages()
    {
        return [
            "required" => "Campo obrigatório",
            "max" => "Excedido limite de caracteres",
            'min' => 'O :attribute deve ter, no mínimo, 5 caracteres',
        ];
    }
}
