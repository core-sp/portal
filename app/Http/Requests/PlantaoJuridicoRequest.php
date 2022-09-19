<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class PlantaoJuridicoRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('PlantaoJuridico');
    }

    public function rules()
    {       
        $arrayHorasDatas = isset(request()->plantaoBloqueio) ? $this->service->getDatasHorasLinkPlantaoAjax(request()->plantaoBloqueio) : null;
        $horarios = isset($arrayHorasDatas) ? '|in:'.implode(',', $arrayHorasDatas['horarios']) : '';
        $datas = isset($arrayHorasDatas) ? $arrayHorasDatas['datas'] : [date('Y-m-d'), date('Y-m-d')];

        return [
            'qtd_advogados' => 'sometimes|required|regex:/^[0-9]{1}$/',
            'horarios' => request('qtd_advogados') == 0 ? 'array' : 'required|array',
            'horarios.*' => 'distinct',
            'dataInicial' => request('qtd_advogados') == 0 ? 'nullable|date' : 'required|nullable|date|after:'.date('Y-m-d'),
            'dataFinal' => request('qtd_advogados') == 0 ? 'nullable|date' : 'required|nullable|date|after_or_equal:dataInicial',
            'plantaoBloqueio' => 'sometimes|required|exists:plantoes_juridicos,id',
            'horariosBloqueio' => 'sometimes|required|array'.$horarios,
            'horariosBloqueio.*' => 'distinct',
            'dataInicialBloqueio' => 'sometimes|required|date|after_or_equal:'.$datas[0].'|before_or_equal:'.$datas[1],
            'dataFinalBloqueio' => 'sometimes|required|date|after_or_equal:dataInicialBloqueio|before_or_equal:'.$datas[1],
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
            'dataInicialBloqueio.after_or_equal' => 'Deve selecionar uma data depois de '.date('d/m/Y').', ou igual ou depois da data inicial do plantão',
            'exists' => 'Este plantão não existe',
            'in' => 'Valor inexistente em horários',
            'before_or_equal' => 'Data deve ser antes ou igual a data final do plantão',
            'date' => 'Deve ser uma data válida',
            'distinct' => 'Existe hora repetida',
            'array' => 'Formato inválido para os horários'
        ];
    }
}
