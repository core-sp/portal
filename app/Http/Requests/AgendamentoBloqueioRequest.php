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
        $horarios = $this->service->getById(request()->idregional)->horariosage;
        
        return [
            'idregional' => 'required|exists:regionais,idregional',
            'diainicio' => 'required|date|after_or_equal:'.date('Y-m-d'),
            'diatermino' => 'date|nullable|after_or_equal:diainicio',
            'horainicio' => 'required|size:5|in:'.$horarios,
            'horatermino' => 'required|size:5|in:'.$horarios,
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo é obrigatório',
            'date' => 'Deve ser uma data válida',
            'diatermino.after_or_equal' => 'Deve ser uma data igual ou maior que a data inicial',
            'diainicio.after_or_equal' => 'Deve ser uma data igual ou maior que hoje',
            'size' => 'Formato de horas inválido',
            'exists' => 'Não existe esse valor',
            'in' => 'Essa hora não existe'
        ];
    }
}
