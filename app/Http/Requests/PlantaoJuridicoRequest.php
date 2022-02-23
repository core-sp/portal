<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlantaoJuridicoRequest extends FormRequest
{
    public function rules()
    {       
        return [
            'qtd_advogados' => 'sometimes|required|regex:/^[0-9]{1}$/',
            'horarios' => request('qtd_advogados') == 0 ? '' : 'required|array',
            'dataInicial' => request('qtd_advogados') == 0 ? '' : 'required|after:'.date('Y-m-d'),
            'dataFinal' => request('qtd_advogados') == 0 ? '' : 'required|after_or_equal:dataInicial',
            'plantaoBloqueio' => 'sometimes|required',
            'horariosBloqueio' => 'sometimes|required|array',
            'dataInicialBloqueio' => 'sometimes|required|after:'.date('Y-m-d'),
            'dataFinalBloqueio' => 'sometimes|required|after_or_equal:dataInicialBloqueio',
        ];
    }

    public function messages()
    {
        return [
            'dataFinal.after_or_equal' => 'Deve selecionar uma data igual ou depois de '.onlyDate(request('dataInicial')),
            'dataInicial.after' => 'Deve selecionar uma data após '.date('d/m/Y'),
            'required' => 'É obrigatório o preenchimento do campo',
            'qtd_advogados.regex' => 'Permitido somente um número de 0 a 9',
            'dataFinalBloqueio.after_or_equal' => 'Deve selecionar uma data igual ou depois de '.onlyDate(request('dataInicialBloqueio')),
            'dataInicialBloqueio.after' => 'Deve selecionar uma data depois de '.date('d/m/Y'),
        ];
    }
}
