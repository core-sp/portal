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
    private $proprio_cpf;
    private $salas_ids;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('SalaReuniao');
    }

    private function regras()
    {
        $participantes = [
            'participantes_cpf' => 'exclude_unless:tipo_sala,reuniao|required_if:tipo_sala,reuniao|array',
            'participantes_cpf.*' => [
                'distinct',
                new Cpf,
                'not_in:'.$this->proprio_cpf,
            ],
            'participantes_nome' => 'exclude_unless:tipo_sala,reuniao|required_if:tipo_sala,reuniao|array|size:'.$this->total_cpfs,
            'participantes_nome.*' => 'distinct|regex:/^\D*$/|min:5|max:191',
            'participante_vetado' => 'nullable|array|size:0',
            'participante_suspenso' => 'nullable|array|size:0',
        ];

        $agendar = [
            'tipo_sala' => 'required|in:reuniao,coworking',
            'sala_reuniao_id' => 'required|in:'.$this->salas_ids,
            'dia' => 'required|date_format:d/m/Y|after:'.date('d\/m\/Y').'|before_or_equal:'.Carbon::today()->addMonth()->format('d/m/Y'),
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
        $this->proprio_cpf = $user->tipoPessoa() == 'PF' ? apenasNumeros($user->cpf_cnpj) : '';
        $this->total_cpfs = 0;
        $this->salas_ids = isset($this->id) ? $this->id : implode(',', $this->service->salasAtivas()->pluck('id')->all());

        if($this->acao == 'agendar')
        {
            if(!$this->filled('tipo_sala') || !$this->filled('sala_reuniao_id') || !$this->filled('dia') || !$this->filled('periodo'))
                return;

            if(!Carbon::hasFormat($this->dia, 'd/m/Y'))
                return;

            $this->disponivel = $this->service->getDiasHoras($this->tipo_sala, $this->sala_reuniao_id, $this->dia, $user);
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
        }

        if($this->acao == 'editar'){
            $temp = $user->agendamentosSalas()->findOrFail($this->id);
            $this->total_cpfs = $temp->sala->getParticipantesAgendar('reuniao');
            $this->merge([
                'tipo_sala' => $temp->tipo_sala,
                'dia' => Carbon::parse($temp->dia)->format('d/m/Y'),
                'periodo' => $temp->periodo,
            ]);
        }

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

        if($this->total_cpfs > 0)
        {
            $vetados = $this->service->site()->participantesVetados($this->dia, $this->periodo, $this->participantes_cpf, $this->id);
            if(!isset($vetados))
                $this->merge(['dia' => '']);
            if(!empty($vetados))
                $this->merge(['participante_vetado' => $vetados]);
            if(empty($vetados))
            {
                $suspensos = $this->service->suspensaoExcecao()->participantesSuspensos($this->participantes_cpf);
                if(isset($suspensos) && !empty($suspensos))
                    $this->merge(['participante_suspenso' => $suspensos]);
            }
        }
    }

    public function rules()
    {
        return $this->regras();
    }

    public function messages()
    {
        $participantesVetados = isset($this->participante_vetado) ? 
        '<br><strong>' . implode('<br>', $this->participante_vetado) . '</strong>' : '';

        $participantesSuspensos = isset($this->participante_suspenso) ? 
        '<br><strong>' . implode('<br>', $this->participante_suspenso) . '</strong>' : '';

        return [
            'required' => 'O campo é obrigatório',
            'required_if' => 'É obrigatório ter participante',
            'sala_reuniao_id.in' => 'Essa sala não está disponível',
            'in' => 'Essa opção não existe',
            'date_format' => 'Formato inválido de dia',
            'after' => 'Não pode agendar no dia de hoje',
            'before_or_equal' => 'Não pode agendar depois de 1 mês',
            'array' => 'Formato inválido do campo Participantes',
            'min' => 'O nome deve ter :min caracteres ou mais',
            'max' => 'O nome deve ter :max caracteres ou menos',
            'regex' => 'Não pode conter número no nome',
            'participantes_cpf.*.distinct' => 'Existe CPF repetido',
            'participantes_nome.*.distinct' => 'Existe nome repetido',
            'size' => 'Total de nomes difere do total de CPFs',
            'mimes' => 'Tipo de arquivo não suportado',
            'anexo_sala.max' => 'O anexo não pode ultrapassar 2MB',
            'justificativa.max' => 'A justificativa deve ter :max caracteres ou menos',
            'justificativa.min' => 'A justificativa deve ter :min caracteres ou mais',
            'participantes_cpf.*.not_in' => 'Representante logado já é um participante. Não pode ser inserido novamente.',
            'participante_vetado.size' => 'Os seguintes participantes já estão agendados neste mesmo dia e período:' . $participantesVetados,
            'participante_suspenso.size' => 'Os seguintes participantes estão suspensos para novos agendamentos:' . $participantesSuspensos,
        ];
    }
}
