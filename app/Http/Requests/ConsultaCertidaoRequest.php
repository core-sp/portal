<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultaCertidaoRequest extends FormRequest
{
    public function rules()
    {
        return [
            "codigo" => "required|min:32",
            "hora" => "required|date_format:H:i",
            "data" => "required|date_format:d/m/Y"
        ];
    }

    public function messages()
    {
        return [
            "codigo.required" => "Código é obrigatório",
            "codigo.min" => "Código deve conter 32 caracteres",
            "hora.required" => "Hora da emissão é obrigatória",
            "hora.date_format" => "Hora da emissão inválida",
            "data.required" => "Data da emissão é obrigatória",
            "data.date_format" => "Data da emissão inválida"
        ];
    }
}
