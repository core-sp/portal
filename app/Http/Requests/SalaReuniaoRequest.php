<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class SalaReuniaoRequest extends FormRequest
{
    private $service;
    private $horas;
    private $required_horas_reuniao;
    private $required_horas_coworking;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('SalaReuniao');
    }

    protected function prepareForValidation()
    {
        $this->horas = todasHoras();

        if($this->filled('hora_limite_final_manha'))
            $this->horas = Arr::where($this->horas, function ($value, $key) {
                $temp = Carbon::parse($this->hora_limite_final_manha);
                $value = Carbon::parse($value);
                return ($value->diffInMinutes($temp) == 0) || ($value->gt($temp) && ($value->diffInMinutes($temp) == 30)) ? false : true;
            });

        if($this->filled('hora_limite_final_tarde'))
            $this->horas = Arr::where($this->horas, function ($value, $key) {
                return $value < $this->hora_limite_final_tarde;
            });

        if(\Route::is('sala.reuniao.horario.formatado'))
            return;

        $this->required_horas_reuniao = $this->participantes_reuniao > 0 ? 'required' : 'required_unless:participantes_reuniao,0';
        $this->required_horas_coworking = $this->participantes_coworking > 0 ? 'required' : 'required_unless:participantes_coworking,0';

        if($this->filled('itens_reuniao') && is_array($this->itens_reuniao))
        {
            $itensReuniao = $this->service->getItensByTipo('reuniao');
            $textos = array();
            foreach($this->itens_reuniao as $val)
                array_push($textos, preg_replace('/[0-9,]/', '', $val));
            foreach($itensReuniao as $val){
                $temp = str_replace('_', '', $val);
                if(in_array($temp, $textos))
                    unset($textos[array_keys($textos, $temp, true)[0]]);
            }
            if(!empty($textos))
                $this->merge([
                    'itens_reuniao' => $this->participantes_reuniao > 0 ? '' : array(),
                ]);
        }
    }

    public function rules()
    {
        return \Route::is('sala.reuniao.horario.formatado') ? 
            [
                'horarios' => 'required|array|in:' . implode(',', $this->horas),
                'horarios.*' => 'distinct',
                'hora_limite_final_manha' => 'nullable|in:' . implode(',', $this->service->getHorasPeriodo('manha')),
                'hora_limite_final_tarde' => 'nullable|in:' . implode(',', $this->service->getHorasPeriodo('tarde'))
            ] :
            [
                'hora_limite_final_manha' => 'required|in:' . implode(',', $this->service->getHorasPeriodo('manha')),
                'hora_limite_final_tarde' => 'required|in:' . implode(',', $this->service->getHorasPeriodo('tarde')),

                'participantes_reuniao' => 'required|integer|not_in:1',
                'horarios_reuniao' => $this->required_horas_reuniao . '|array|in:' . implode(',', $this->horas),
                'horarios_reuniao.*' => 'distinct',
                'itens_reuniao' => 'required_unless:participantes_reuniao,0|array',
                'itens_reuniao.*' => 'distinct',

                'participantes_coworking' => 'required|integer',
                'horarios_coworking' => $this->required_horas_coworking . '|array|in:' . implode(',', $this->horas),
                'horarios_coworking.*' => 'distinct',
                'itens_coworking' => 'required_unless:participantes_coworking,0|array|in:' . implode(',', $this->service->getItensByTipo('coworking')),
                'itens_coworking.*' => 'distinct',
            ];
    }

    public function messages()
    {
        return [
            'itens_reuniao.required_unless' => 'O campo é obrigatório / itens editáveis não alterados ou com erro',
            'itens_coworking.required_unless' => 'O campo é obrigatório / itens editáveis não alterados ou com erro',
            'required' => 'O campo é obrigatório',
            'required_unless' => 'O campo é obrigatório se participantes maior que 0',
            'horarios_reuniao.required_if' => 'O campo é obrigatório se participantes maior que 0',
            'horarios_coworking.required_if' => 'O campo é obrigatório se participantes maior que 0',
            'in' => 'Esse valor não existe ou não pode ser inserido',
            'array' => 'Formato inválido',
            'integer' => 'Deve ser um número',
            'distinct' => 'Existe valor repetido',
            'participantes_reuniao.not_in' => 'Deve ter pelo menos 2 participantes em reunião, ou 0 para tornar indisponível'
        ];
    }
}
