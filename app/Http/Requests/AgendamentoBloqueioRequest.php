<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgendamentoBloqueioRequest extends FormRequest
{
    public function rules()
    {
        return [
            'idregional' => 'required',
            'horainicio' => 'required',
            'horatermino' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'idregional.required' => 'Selecione uma regional',
            'horainicio.required' => 'Seleciona uma hora de início para o bloqueio',
            'horatermino.required' => 'Seleciona uma hora de término para o bloqueio',
        ];
    }
}
