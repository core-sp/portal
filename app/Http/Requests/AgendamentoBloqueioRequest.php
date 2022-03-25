<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class AgendamentoBloqueioRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Regional');
    }

    public function rules()
    {
        $regional = isset(request()->idregional) ? $this->service->getById(request()->idregional) : null;
        $horarios = isset($regional->horariosage) ? $regional->horariosage : null;
        $ageporhorario = isset($regional->ageporhorario) ? $regional->ageporhorario : null;
        
        return [
            'idregional' => 'required|exists:regionais,idregional',
            'diainicio' => 'required|date|after_or_equal:'.date('Y-m-d'),
            'diatermino' => 'date|nullable|after_or_equal:diainicio',
            'horarios' => 'required|array|in:'.$horarios,
            'qtd_atendentes' => 'required|numeric|min:0|max:'.$ageporhorario
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo é obrigatório',
            'date' => 'Deve ser uma data válida',
            'diatermino.after_or_equal' => 'Deve ser uma data igual ou maior que a data inicial',
            'diainicio.after_or_equal' => 'Deve ser uma data igual ou maior que hoje',
            'exists' => 'Não existe esse valor',
            'in' => 'Essa hora não existe',
            'array' => 'Formato inválido',
            'numeric' => 'Deve ser um número',
            'min' => 'Valor mínimo é :min',
            'max' => 'Valor máximo é :max'
        ];
    }
}
