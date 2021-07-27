<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepresentanteEnderecoRequest extends FormRequest
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
            "municipio" => "required|max:30",
            "crimage" => "required|mimes:jpeg,png,jpg,pdf|max:2048",
            "crimagedois" => "mimes:jpeg,png,jpg,pdf|max:2048"
        ];
    }

    public function messages()
    {
        return [
            "required" => "Campo obrigatório",
            "max" => "Excedido limite de caracteres",
            "crimage.required" => "Favor adicionar um comprovante de residência",
            "mimes" => "Tipo de arquivo não suportado",
            "max" => "A imagem não pode ultrapassar 2MB"
        ];
    }
}
