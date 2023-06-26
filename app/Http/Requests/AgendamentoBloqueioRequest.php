<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class AgendamentoBloqueioRequest extends FormRequest
{
    private $service;
    private $chaveRegional;
    private $chaveHorarios;
    private $ageporhorario;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        if(\Route::is('sala.reuniao.*'))
        {
            $this->service = $this->service->getService('SalaReuniao');
            $this->chaveRegional = isset($this->id) ? $this->sala_reuniao_id : implode(',', $this->service->salasAtivas()->pluck('id')->all());
            $this->chaveHorarios = implode(',', $this->service->getTodasHorasById($this->sala_reuniao_id));

            return;
        }

        $this->service = $this->service->getService('Regional');
        $this->chaveRegional = 'required|exists:regionais,idregional';
        $this->chaveHorarios = 'required|array|in:';

        if(request()->filled('idregional') && ($this->idregional == '14'))
            $this->merge(['idregional' => '0']);

        $regional = isset(request()->idregional) && (request()->idregional != 'Todas') ? $this->service->getById(request()->idregional) : null;
        $this->chaveHorarios .= isset($regional->horariosage) ? $regional->horariosage : '';
        $this->ageporhorario = isset($regional->ageporhorario) ? $regional->ageporhorario - 1 : 1;

        if(\Route::is('agendamentobloqueios.store'))
            if(request()->filled('idregional') && (request()->idregional == 'Todas'))
            {
                $this->chaveRegional = 'required|in:Todas';
                $this->chaveHorarios = '';
                $this->ageporhorario = 0;
            }
    }

    public function rules()
    {
        if(\Route::is('sala.reuniao.*'))
            return [
                'sala_reuniao_id' => 'required|in:'.$this->chaveRegional,
                'horarios' => 'required|array|in:'.$this->chaveHorarios,
                'horarios.*' => 'distinct',
                'dataInicial' => 'required|date|after_or_equal:'.now()->addDay()->format('Y-m-d'),
                'dataFinal' => 'nullable|date|after_or_equal:dataInicial',
            ];

        return [
            'idregional' => $this->chaveRegional,
            'diainicio' => 'required|date|after_or_equal:'.date('Y-m-d'),
            'diatermino' => 'date|nullable|after_or_equal:diainicio',
            'horarios' => $this->chaveHorarios,
            'horarios.*' => 'distinct',
            'qtd_atendentes' => 'required|numeric|min:0|max:'.$this->ageporhorario
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo é obrigatório',
            'date' => 'Deve ser uma data válida',
            'diatermino.after_or_equal' => 'Deve ser uma data igual ou maior que a data de início',
            'diainicio.after_or_equal' => 'Deve ser uma data igual ou maior que hoje',
            'exists' => 'Não existe esse valor',
            'in' => 'Essa hora não existe',
            'array' => 'Formato inválido',
            'numeric' => 'Deve ser um número',
            'min' => 'Valor mínimo é :min',
            'qtd_atendentes.max' => 'Deve ser um valor máximo de: total de atendentes atual - 1. Se opção selecionada é "Todas", deve ser 0',
            'distinct' => 'Existe hora repetida',
            'sala_reuniao_id.in' => 'Essa sala está indisponível',
            'horarios.in' => 'Essa hora está indisponível',
            'dataInicial.after_or_equal' => 'Deve ser uma data igual ou maior que amanhã',
            'dataFinal.after_or_equal' => 'Deve ser uma data igual ou maior que data inicial ou sem data para tempo indeterminado',
        ];
    }
}
