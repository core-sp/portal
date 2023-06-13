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

    private function regras()
    {
        $participantes = [
            'participantes_cpf' => 'exclude_if:tipo_sala,coworking|required_if:tipo_sala,reuniao|array',
            'participantes_cpf.*' => [
                'distinct',
                new Cpf,
            ],
            'participantes_nome' => 'exclude_if:tipo_sala,coworking|required_if:tipo_sala,reuniao|array|size:'.$this->total_cpfs,
            'participantes_nome.*' => 'distinct|regex:/^\D*$/|min:5|max:191',
        ];

        $agendar = [
            'sala_reuniao_id' => 'required|exists:salas_reunioes,id',
            'tipo_sala' => 'required|in:reuniao,coworking',
            'dia' => 'required|date_format:d/m/Y|after:'.date('d\/m\/Y').'|before_or_equal:'.Carbon::today()->addMonth()->format('d\/m\/Y'),
            'periodo' => 'required|in:manha,tarde',
        ];

        $justificar = [
            'justificativa' => 'required|max:1000|min:10',
            'anexo_sala' => 'nullable|mimes:jpeg,jpg,png,pdf|max:2048'
        ];

        if($this->acao == 'agendar')
            return array_merge($agendar, $participantes);
        if($this->acao == 'editar')
            return $participantes;
        if($this->acao == 'cancelar')
            return array();
        if($this->acao == 'justificar')
            return $justificar;
    }

    protected function prepareForValidation()
    {
        if(($this->acao == 'cancelar') || ($this->acao == 'justificar'))
            return;

        $user = auth()->guard('representante')->user();
        if($this->acao == 'agendar')
        {
            $this->disponivel = $this->service->getDiasHoras($this->tipo_sala, $this->sala_reuniao_id, $this->dia);
            $this->total_cpfs = isset($this->disponivel['total']) ? $this->disponivel['total'] : 0;

            if(!isset($this->disponivel) || (!isset($this->disponivel['manha']) && !isset($this->disponivel['tarde']))){
                $this->merge([
                    'dia' => '',
                    'periodo' => '',
                ]);
                return;
            }
    
            if((($this->periodo == 'manha') && !isset($this->disponivel['manha'])) || (($this->periodo == 'tarde') && !isset($this->disponivel['tarde'])))
                $this->merge(['periodo' => '']);

            if(!$user->podeAgendarByDiaPeriodo($this->dia, $this->periodo))
                $this->merge([
                    'dia' => '',
                    'periodo' => '',
                ]);
        }

        if($this->acao == 'editar')
            $this->total_cpfs = $user->agendamentosSalas()->find($this->id)->sala->getParticipantesAgendar('reuniao');

        if(is_array($this->participantes_cpf) && is_array($this->participantes_nome) && ($this->total_cpfs > 0))
        {
            $nomes = array_filter($this->participantes_nome);
            $cpfs = array_filter($this->participantes_cpf);
            foreach($cpfs as $key => $cpf){
                $cpfs[$key] = apenasNumeros($cpf);
                if(isset($nomes[$key]))
                    $nomes[$key] = mb_strtoupper($nomes[$key], 'UTF-8');
            }
            $this->merge([
                'participantes_cpf' => $this->total_cpfs < count($cpfs) ? array() : $cpfs,
                'participantes_nome' => $this->total_cpfs < count($cpfs) ? array() : $nomes,
            ]);
            $this->total_cpfs = $this->total_cpfs < count($cpfs) ? 0 : count($cpfs);
        }
    }

    public function rules()
    {
        return $this->regras();
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
            'mimes' => 'Tipo de arquivo não suportado',
            'anexo_sala.max' => 'O anexo não pode ultrapassar 2MB',
            'justificativa.max' => 'A justificativa deve ter :max caracteres ou menos',
            'justificativa.min' => 'A justificativa deve ter :min caracteres ou mais',
        ];
    }
}
