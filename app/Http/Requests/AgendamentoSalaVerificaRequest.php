<?php

namespace App\Http\Requests;

use App\Contracts\MediadorServiceInterface;
use App\Rules\CpfCnpj;
use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\GerentiRepositoryInterface;

class AgendamentoSalaVerificaRequest extends FormRequest
{
    private $service;
    private $gerentiRepository;
    private $salas;

    public function __construct(MediadorServiceInterface $service, GerentiRepositoryInterface $gerentiRepository)
    {
        $this->service = $service;
        $this->gerentiRepository = $gerentiRepository;
    }

    protected function prepareForValidation()
    {
        $dados = [];
        $campos = ["NOME" => 'nomeGerenti', "REGISTRONUM" => 'registroGerenti', "EMAILS" => 'emailGerenti', "SITUACAO" => 'situacaoGerenti'];

        if($this->filled('cpf_cnpj') && ((strlen($this->cpf_cnpj) == 14) || (strlen($this->cpf_cnpj) == 18))){
            $dados = $this->gerentiRepository->gerentiAtivo(apenasNumeros($this->cpf_cnpj));

            foreach($campos as $key => $value){
                if(!isset($dados[0][$key]))
                    $this->merge([$value => null]);
                else
                    $this->merge([
                        $value => $value == 'emailGerenti' ? explode(';', $dados[0][$key])[0] : $dados[0][$key]
                    ]);
            }
            if(isset($dados[0]["ASS_ID"])){
                $status = trim(explode(':', $this->gerentiRepository->gerentiStatus($dados[0]["ASS_ID"]))[1]);
                $this->merge(['situacaoGerenti' => $this->situacaoGerenti . ', ' . $status]);
            }
        }

        if($this->filled('sala_reuniao_id')){
            $this->merge(['sala_reuniao_id' => in_array(auth()->user()->idperfil, [8, 21]) ? auth()->user()->idregional : $this->sala_reuniao_id]);
            $this->salas = $this->service->getService('SalaReuniao')->salasAtivas();
            $sala = $this->salas->where('id', $this->sala_reuniao_id)->first();
            $total = isset($sala) ? $sala->participantes_reuniao - 1 : 0;
            $this->merge(['total_participantes' => $total]);
        }

        if(\Route::is('sala.reuniao.agendados.verifica.criar') && isset($this->participantes_cpf) && is_array($this->participantes_cpf))
        {
            $suspensos = $this->service->getService('SalaReuniao')->suspensaoExcecao()->participantesSuspensos($this->participantes_cpf);
            $textoSuspensos = isset($suspensos) && !empty($suspensos) && (count($suspensos) == 1) ? 
            'O seguinte participante está suspenso para novos agendamentos na área restrita do representante:' :
            'Os seguintes participantes estão suspensos para novos agendamentos na área restrita do representante:';
            $participantesSuspensos = isset($suspensos) && !empty($suspensos) ? 
            '<br><strong>' . implode('<br>', $suspensos) . '</strong>' : '';

            isset($suspensos) && !empty($suspensos) ? $this->merge(['suspenso' => $textoSuspensos . $participantesSuspensos]) : $this->merge(['suspenso' => '']);
        }

        if(!\Route::is('sala.reuniao.agendados.verifica.criar'))
        {
            $this->merge(['total_participantes' => isset($this->total_participantes) ? $this->total_participantes : 0]);
            $this->merge(['cpf_cnpj' => apenasNumeros($this->cpf_cnpj)]);
            $campos = ["NOME" => 'nome', "REGISTRONUM" => 'registro_core', "EMAILS" => 'email', "ASS_ID" => 'ass_id'];
            foreach($campos as $key => $value)
                isset($dados[0][$key]) ? $this->merge([$value => $dados[0][$key]]) : $this->merge([$value => null]);

            if(is_array($this->participantes_cpf) && is_array($this->participantes_nome))
            {
                $nomes = array_filter($this->participantes_nome);
                $cpfs = array_filter($this->participantes_cpf);
                foreach($cpfs as $key => $cpf){
                    $cpfs[$key] = apenasNumeros($cpf);
                    if(isset($nomes[$key]))
                        $nomes[$key] = mb_strtoupper($nomes[$key], 'UTF-8');
                }
                $this->merge([
                    'participantes_cpf' => $this->total_participantes < count($cpfs) ? array() : $cpfs,
                    'participantes_nome' => $this->total_participantes < count($cpfs) ? array() : $nomes,
                    'total_participantes' => count($cpfs),
                ]);
            }
        }
    }

    public function rules()
    {        
        if(!\Route::is('sala.reuniao.agendados.verifica.criar'))
            return [
                'cpf_cnpj' => ['required', new CpfCnpj],
                'tipo_sala' => 'required|in:reuniao,coworking',
                'sala_reuniao_id' => 'required|in:'.implode(',', $this->salas->pluck('id')->all()),
                'dia' => 'required|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
                'periodo_entrada' => 'required|date_format:H:i|after:08:59|before:17:31',
                'periodo_saida' => 'required|date_format:H:i|after:periodo_entrada|before:18:01',
                'participantes_cpf' => 'exclude_unless:tipo_sala,reuniao|required_if:tipo_sala,reuniao|array',
                'participantes_cpf.*' => ['distinct', new Cpf, 'not_in:'.$this->cpf_cnpj],
                'participantes_nome' => 'exclude_unless:tipo_sala,reuniao|required_if:tipo_sala,reuniao|array|size:'.$this->total_participantes,
                'participantes_nome.*' => 'distinct|regex:/^\D*$/|min:5|max:191',
                'nome' => 'required',
                'registro_core' => 'required',
                'email' => '',
                'ass_id' => 'required',
            ];

        if($this->filled('cpf_cnpj'))
            return [
                'nomeGerenti' => '',
                'registroGerenti' => '',
                'emailGerenti' => '',
                'situacaoGerenti' => '',
            ];

        if($this->filled('sala_reuniao_id'))
            return [
                'total_participantes' => '',
            ];

        if($this->filled('participantes_cpf'))
            return [
                'suspenso' => '',
            ];
    }

    public function messages() 
    {
        return [
            'required' => 'O campo é obrigatório',
            'required_if' => 'É obrigatório ter participante',
            'sala_reuniao_id.in' => 'Essa sala não está disponível',
            'in' => 'Essa opção não existe ou não está disponível',
            'date_format' => 'Formato inválido',
            'dia.before_or_equal' => 'Não pode agendar após a data de hoje',
            'array' => 'Formato inválido',
            'min' => 'O campo deve ter :min caracteres ou mais',
            'max' => 'O campo deve ter :max caracteres ou menos',
            'regex' => 'Não pode conter número no nome',
            'participantes_cpf.*.distinct' => 'Existe CPF repetido',
            'participantes_nome.*.distinct' => 'Existe nome repetido',
            'size' => 'Total de nomes difere do total de CPFs',
            'periodo_entrada.after' => 'Deve ser a partir das 09:00',
            'periodo_entrada.before' => 'Deve ser até as 17:30',
            'periodo_saida.after' => 'Deve ser depois do horário da entrada',
            'periodo_saida.before' => 'Deve ser até as 18:00',
        ];
    }
}
