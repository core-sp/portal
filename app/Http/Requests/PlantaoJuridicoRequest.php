<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlantaoJuridicoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'qtd_advogados' => 'required|regex:/^[0-9]{1}$/',
            'horarios' => request('qtd_advogados') == 0 ? '' : 'required',
            'dataInicial' => request('qtd_advogados') == 0 ? '' : 'required|after:'.date('Y-m-d'),
            'dataFinal' => request('qtd_advogados') == 0 ? '' : 'required|after_or_equal:'.request('dataInicial'),
        ];
    }

    public function messages()
    {
        return [
            'dataFinal.after_or_equal' => 'Deve selecionar uma data igual ou depois de '.onlyDate(request('dataInicial')),
            'dataInicial.after' => 'Deve selecionar uma data após '.date('d/m/Y'),
            'required' => 'É obrigatório o preenchimento do campo',
            'qtd_advogados.regex' => 'Permitido somente um número de 0 a 9',
        ];
    }
}
