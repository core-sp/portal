<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitaCedulaRequest extends FormRequest
{
    public function rules()
    {
        return [
            "cep" => "required",
            "bairro" => "required|max:100",
            "logradouro" => "required|max:100",
            "numero" => "required|max:15",
            "complemento" => "max:100",
            "estado" => "required|max:5",
            "municipio" => "required|max:100"
        ];
    }

    public function messages()
    {
        return [
            "required" => "Campo obrigatório",
            "max" => "Excedido limite de caracteres",
        ];
    }
}
