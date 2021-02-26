<?php

namespace App\Http\Requests;

use App\Rules\RecaptchaRequired;
use Illuminate\Foundation\Http\FormRequest;

class ConsultaCertidaoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'codigo' => 'required',
            'numero' => 'required',
            'hora' => 'required|date_format:H:i:s',
            'data' => 'required|date_format:d/m/Y',
            'g-recaptcha-response' => [new RecaptchaRequired, 'recaptcha']
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Código é obrigatório',
            'numero.required' => 'Número é obrigatório',
            'codigo.min' => 'Código deve conter 32 caracteres',
            'hora.required' => 'Hora da emissão é obrigatória',
            'hora.date_format' => 'Hora da emissão inválida',
            'data.required' => 'Data da emissão é obrigatória',
            'data.date_format' => 'Data da emissão inválida',
            'g-recaptcha-response.recaptcha' => 'ReCAPTCHA inválido'
        ];
    }
}
