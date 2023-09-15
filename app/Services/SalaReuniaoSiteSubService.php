<?php

namespace App\Services;

use App\Contracts\SalaReuniaoSiteSubServiceInterface;
use App\AgendamentoSala;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoSalaMail;
use Carbon\Carbon;

class SalaReuniaoSiteSubService implements SalaReuniaoSiteSubServiceInterface {

    public function verificaSuspensao($user, $service, $acao = '')
    {
        $suspenso = $service->getService('SalaReuniao')->suspensaoExcecao()->verificaSuspenso($user->cpf_cnpj);
        $retornoSuspensao = null;
        $retornoExcecao = null;

        if(isset($suspenso))
        {
            $liberado = $suspenso->updateRelacaoByIdRep($user->id)->isLiberadoHoje();
            if(!$liberado)
            {
                $justificativa = $suspenso->getJustificativasDesc($suspenso->getJustificativasByAcao('suspensão'))[0];
                $texto = '<i class="fas fa-ban"></i>&nbsp;&nbsp;Está suspenso pelo período de <b>' . $suspenso->mostraPeriodo().'</b>';
                $texto .= '<br><br>Durante a suspensão não pode criar novos agendamentos e nem participar de novas reuniões.';
                $texto .= '<br>Os agendamentos e participações já criados não são afetados.';
                $texto .= '<br><b>Última justificativa de suspensão:</b> '.$suspenso->removeNomeAcaoJustificativa($justificativa, 'suspensão');
                $retornoSuspensao = [
                    'message' => $texto,
                    'class' => 'alert-danger'
                ];
            }

            if($liberado)
            {
                $texto = '<i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Está liberado temporariamente pelo período de <b>'.$suspenso->mostraPeriodoExcecao().'</b>';
                $retornoExcecao = [
                    'message' => $texto . ' o acesso para criar novos agendamentos e participar de novas reuniões, independentemente do dia do agendamento.',
                    'class' => 'alert-info'
                ];
            }
                
            switch ($acao) {
                case 'suspensão':
                    return $retornoSuspensao;
                    break;
                case 'exceção':
                    return $retornoExcecao;
                    break;
                default:
                    if(isset($retornoSuspensao['message']))
                        return $retornoSuspensao;
                    if(isset($retornoExcecao['message']))
                        return $retornoExcecao;
                    break;
            }
        }
    }

    public function verificaPodeAgendar($user, $service, $mes = null, $ano = null)
    {
        $situacao = $this->verificaSuspensao($user, $service, 'suspensão');
        if(isset($situacao['message']))
            return $situacao;

        if(!$user->podeAgendar($mes, $ano))
            return [
                'message' => '<i class="fas fa-times"></i>&nbsp;&nbsp;Já possui o limite de 4 agendamentos confirmados ou com presença a confirmar no mês atual e/ou seguinte.',
                'class' => 'alert-danger'
            ];
    }

    public function save($dados, $user, $service)
    {
        if(!Carbon::hasFormat($dados['dia'], 'd/m/Y'))
            return [
                'message' => 'Data no formato inválido', 
                'class' => 'alert-danger'
            ];
        $dia = Carbon::createFromFormat('d/m/Y', $dados['dia']);

        $resultado = $this->verificaPodeAgendar($user, $service, $dia->month, $dia->year);
        if(isset($resultado['message']))
            return $resultado;

        $participantes = null;
        $dia = $dia->format('Y-m-d');
        if($dados['tipo_sala'] == 'reuniao')
            $participantes = json_encode(
                array_combine($dados['participantes_cpf'], $dados['participantes_nome']), JSON_FORCE_OBJECT
            );
        $protocolo = AgendamentoSala::getProtocolo();

        $agendamento = AgendamentoSala::create([
            'idrepresentante' => $user->id,
            'sala_reuniao_id' => $dados['sala_reuniao_id'],
            'participantes' => $participantes,
            'dia' => $dia,
            'periodo' => $dados['periodo'],
            'periodo_todo' => $dados['periodo_todo'],
            'tipo_sala' => $dados['tipo_sala'],
            'protocolo' => $protocolo,
        ]);

        $termo = $agendamento->termos()->create([
            'ip' => $dados['ip']
        ]);

        $string = $user->nome.' (CPF / CNPJ: '.$user->cpf_cnpj.') *agendou* reserva da sala em *'.$agendamento->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia).' para '.$agendamento->tipo_sala.', no período ' .$agendamento->periodo.' e ' .$termo->message();
        event(new ExternoEvent($string));

        Mail::to($user->email)->queue(new AgendamentoSalaMail($agendamento));
    }

    public function verificaPodeEditar($id, $user)
    {
        $agendamento = $user->agendamentosSalas()->findOrFail($id);
        if($agendamento->podeEditarParticipantes())
            return ['agendamento' => $agendamento];
        return [
            'message' => '<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível editar o agendamento.',
            'class' => 'alert-danger'
        ];
    }

    public function editarParticipantes($dados, $id, $user)
    {
        $resultado = $this->verificaPodeEditar($id, $user);
        if(isset($resultado['message']))
            return $resultado;

        $agendamento = $resultado['agendamento'];

        $participantes = json_encode(
            array_combine($dados['participantes_cpf'], $dados['participantes_nome']), JSON_FORCE_OBJECT
        );

        $agendamento->participantes = $participantes;

        if($agendamento->isClean('participantes'))
            return [
                'message' => '<i class="fas fa-info-circle"></i>&nbsp;&nbsp;Não houve alterações nos participantes.',
                'class' => 'alert-info'
            ];

        $agendamento->update([
            'participantes' => $participantes,
        ]);

        $string = $user->nome.' (CPF / CNPJ: '.$user->cpf_cnpj.') *editou os participantes* da reserva da sala em *'.$agendamento->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia).' para '.$agendamento->tipo_sala.', no período ' .$agendamento->periodo;
        event(new ExternoEvent($string));

        Mail::to($user->email)->queue(new AgendamentoSalaMail($agendamento->fresh(), 'editar'));
    }

    public function verificaPodeCancelar($id, $user)
    {
        $agendamento = $user->agendamentosSalas()->findOrFail($id);
        if($agendamento->podeCancelar())
            return ['agendamento' => $agendamento];
        return [
            'message' => '<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível cancelar o agendamento.',
            'class' => 'alert-danger'
        ];
    }

    public function cancelar($id, $user)
    {
        $resultado = $this->verificaPodeCancelar($id, $user);
        if(isset($resultado['message']))
            return $resultado;

        $agendamento = $resultado['agendamento'];

        $agendamento->update([
            'status' => AgendamentoSala::STATUS_CANCELADO,
        ]);

        $string = $user->nome.' (CPF / CNPJ: '.$user->cpf_cnpj.') *cancelou* a reserva da sala em *'.$agendamento->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia).' para '.$agendamento->tipo_sala.', no período ' .$agendamento->periodo;
        event(new ExternoEvent($string));
    }

    public function verificaPodeJustificar($id, $user)
    {
        $agendamento = $user->agendamentosSalas()->findOrFail($id);
        if($agendamento->podeJustificar())
            return ['agendamento' => $agendamento];
        return [
            'message' => '<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível justificar o agendamento.',
            'class' => 'alert-danger'
        ];
    }

    public function justificar($dados, $id, $user)
    {
        $resultado = $this->verificaPodeJustificar($id, $user);
        if(isset($resultado['message']))
            return $resultado;

        $agendamento = $resultado['agendamento'];

        $anexo = null;
        if(isset($dados['anexo_sala']))
        {
            $anexo = $user->id . '-' . time() . '.' . $dados['anexo_sala']->getClientOriginalExtension();
            $dados['anexo_sala']->storeAs("representantes/agendamento_sala", $anexo);
        }

        $agendamento->update([
            'justificativa' => $dados['justificativa'],
            'anexo' => $anexo,
            'status' => AgendamentoSala::STATUS_ENVIADA,
        ]);

        $string = $user->nome.' (CPF / CNPJ: '.$user->cpf_cnpj.') *justificou e está em análise do atendente* o não comparecimento do agendamento da sala em *'.$agendamento->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia).' para '.$agendamento->tipo_sala.', no período ' .$agendamento->periodo;
        event(new ExternoEvent($string));

        Mail::to($user->email)->queue(new AgendamentoSalaMail($agendamento->fresh(), 'justificar'));
    }

    public function participantesVetados($dia, $periodo, $array_cpfs, $id = null)
    {
        if(!Carbon::hasFormat($dia, 'd/m/Y') && !Carbon::hasFormat($dia, 'Y-m-d'))
            return null;
            
        if(Carbon::hasFormat($dia, 'd/m/Y'))
            $dia = Carbon::createFromFormat('d/m/Y', $dia)->format('Y-m-d');
        $vetados = AgendamentoSala::participantesVetados($dia, $periodo, $array_cpfs, $id);

        if(!empty($vetados))
            foreach($vetados as $chave => $val)
                $vetados[$chave] = formataCpfCnpj($val);

        return $vetados;
    }

    public function getAgendadosParticipante($user)
    {
        if($user->tipoPessoa() == 'PF')
            return AgendamentoSala::getAgendadoParticipanteByCpf($user->cpf_cnpj);
        return collect();
    }
}