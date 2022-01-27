<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlantaoJuridicoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'dia' => 'required|after:'.date('Y-m-d'),
            'duracao' => 'required',
            'intervalo' => 'required',
            'horarioInicial' => 'required',
            'horarioFinal' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'after' => 'Deve selecionar uma data após '.date('d/m/Y'),
            'required' => 'É obrigatório o preenchimento do campo',
        ];
    }
}
