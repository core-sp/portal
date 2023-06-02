<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class SalaReuniaoRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('SalaReuniao');
    }

    protected function prepareForValidation()
    {
        if($this->filled('itens_reuniao'))
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
                    'itens_reuniao' => '',
                ]);
        }
    }

    public function rules()
    {
        return [
            'participantes_reuniao' => 'required|integer',
            'manha_horarios_reuniao' => 'exclude_if:participantes_reuniao,0|required|array|in:' . implode(',', array_slice(todasHoras(), 0, 7)),
            'manha_horarios_reuniao.*' => 'distinct',
            'tarde_horarios_reuniao' => 'exclude_if:participantes_reuniao,0|required|array|in:' . implode(',', array_slice(todasHoras(), 7)),
            'tarde_horarios_reuniao.*' => 'distinct',
            'itens_reuniao' => 'exclude_if:participantes_reuniao,0|required|array',
            'itens_reuniao.*' => 'distinct',

            'participantes_coworking' => 'required|integer',
            'manha_horarios_coworking' => 'exclude_if:participantes_coworking,0|required|array|in:' . implode(',', array_slice(todasHoras(), 0, 7)),
            'manha_horarios_coworking.*' => 'distinct',
            'tarde_horarios_coworking' => 'exclude_if:participantes_coworking,0|required|array|in:' . implode(',', array_slice(todasHoras(), 7)),
            'tarde_horarios_coworking.*' => 'distinct',
            'itens_coworking' => 'exclude_if:participantes_coworking,0|required|array|in:' . implode(',', $this->service->getItensByTipo('coworking')),
            'itens_coworking.*' => 'distinct',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo é obrigatório',
            'in' => 'Esse valor não existe',
            'array' => 'Formato inválido',
            'integer' => 'Deve ser um número',
            'distinct' => 'Existe valor repetido'
        ];
    }
}
