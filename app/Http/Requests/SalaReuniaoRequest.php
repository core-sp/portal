<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

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
        $this->horas = $this->service->getHoras();

        $this->required_horas_reuniao['manha'] = $this->participantes_reuniao > 0 ? 'required_if:tarde_horarios_reuniao,' : 'required_unless:participantes_reuniao,0';
        $this->required_horas_reuniao['tarde'] = $this->participantes_reuniao > 0 ? 'required_if:manha_horarios_reuniao,' : 'required_unless:participantes_reuniao,0';

        $this->required_horas_coworking['manha'] = $this->participantes_coworking > 0 ? 'required_if:tarde_horarios_coworking,' : 'required_unless:participantes_coworking,0';
        $this->required_horas_coworking['tarde'] = $this->participantes_coworking > 0 ? 'required_if:manha_horarios_coworking,' : 'required_unless:participantes_coworking,0';

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
        return [
            'participantes_reuniao' => 'required|integer|not_in:1',
            'manha_horarios_reuniao' => $this->required_horas_reuniao['manha'] . '|array|in:' . implode(',', $this->horas['manha']),
            'manha_horarios_reuniao.*' => 'distinct',
            'tarde_horarios_reuniao' => $this->required_horas_reuniao['tarde'] . '|array|in:' . implode(',', $this->horas['tarde']),
            'tarde_horarios_reuniao.*' => 'distinct',
            'itens_reuniao' => 'required_unless:participantes_reuniao,0|array',
            'itens_reuniao.*' => 'distinct',

            'participantes_coworking' => 'required|integer',
            'manha_horarios_coworking' => $this->required_horas_coworking['manha'] . '|array|in:' . implode(',', $this->horas['manha']),
            'manha_horarios_coworking.*' => 'distinct',
            'tarde_horarios_coworking' => $this->required_horas_coworking['tarde'] . '|array|in:' . implode(',', $this->horas['tarde']),
            'tarde_horarios_coworking.*' => 'distinct',
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
            'manha_horarios_reuniao.required_if' => 'O campo é obrigatório se participantes maior que 0 e horarios tarde não selecionados',
            'tarde_horarios_reuniao.required_if' => 'O campo é obrigatório se participantes maior que 0 e horarios manhã não selecionados',
            'manha_horarios_coworking.required_if' => 'O campo é obrigatório se participantes maior que 0 e horarios tarde não selecionados',
            'tarde_horarios_coworking.required_if' => 'O campo é obrigatório se participantes maior que 0 e horarios manhã não selecionados',
            'in' => 'Esse valor não existe',
            'array' => 'Formato inválido',
            'integer' => 'Deve ser um número',
            'distinct' => 'Existe valor repetido',
            'participantes_reuniao.not_in' => 'Deve ter pelo menos 2 participantes em reunião, ou 0 para tornar indisponível'
        ];
    }
}
