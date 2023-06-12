<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\Cpf;
use Carbon\Carbon;

class RepSalaReuniaoRequest extends FormRequest
{
    private $service;
    private $disponivel;
    private $total_cpfs;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('SalaReuniao');
    }

    protected function prepareForValidation()
    {
        $this->disponivel = $this->service->getDiasHoras($this->tipo_sala, $this->sala_reuniao_id, $this->dia);
        $this->total_cpfs = 0;

        if(!isset($this->disponivel) || (!isset($this->disponivel['manha']) && !isset($this->disponivel['tarde']))){
            $this->merge([
                'dia' => '',
                'periodo' => '',
            ]);
            return;
        }

        if((($this->periodo == 'manha') && !isset($this->disponivel['manha'])) || (($this->periodo == 'tarde') && !isset($this->disponivel['tarde'])))
            $this->merge(['periodo' => '']);

        if(is_array($this->participantes_cpf) && is_array($this->participantes_nome))
        {
            $nomes = array_filter($this->participantes_nome);
            $cpfs = array_filter($this->participantes_cpf);
            foreach($cpfs as $key => $cpf){
                $cpfs[$key] = apenasNumeros($cpf);
                $nomes[$key] = !isset($nomes[$key]) ? "" : mb_strtoupper($nomes[$key], 'UTF-8');
            }
            $this->total_cpfs = count($cpfs);
            $this->merge([
                'participantes_cpf' => $cpfs,
                'participantes_nome' => $nomes,
            ]);
        }
    }

    public function rules()
    {
        return [
            'sala_reuniao_id' => 'required|exists:salas_reunioes,id',
            'tipo_sala' => 'required|in:reuniao,coworking',
            'dia' => 'required|date_format:d/m/Y|after:'.date('d\/m\/Y').'|before_or_equal:'.Carbon::today()->addMonth()->format('d\/m\/Y'),
            'periodo' => 'required|in:manha,tarde',
            'participantes_cpf' => 'exclude_if:tipo_sala,coworking|required_if:tipo_sala,reuniao|array',
            'participantes_cpf.*' => [
                'distinct',
                new Cpf,
            ],
            'participantes_nome' => 'exclude_if:tipo_sala,coworking|required_if:tipo_sala,reuniao|array|size:'.$this->total_cpfs,
            'participantes_nome.*' => 'distinct|regex:/^\D*$/|min:5|max:191',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo é obrigatório',
            'required_if' => 'É obrigatório ter participante',
            'exists' => 'Essa sala não existe',
            'in' => 'Essa opção não existe',
            'date_format' => 'Formato inválido de dia',
            'after' => 'Não pode agendar no dia de hoje',
            'before_or_equal' => 'Não pode agendar depois de 1 mês',
            'array' => 'Formato inválido do campo Participantes',
            'min' => 'O nome deve ter :min caracteres ou mais',
            'max' => 'O nome deve ter :max caracteres ou menos',
            'regex' => 'Não pode conter número no nome',
            'distinct' => 'Existe valor repetido',
            'size' => 'Total de nomes difere do total de CPFs',
        ];
    }
}
