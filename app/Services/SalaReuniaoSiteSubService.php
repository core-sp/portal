<?php

namespace App\Services;

use App\Contracts\SalaReuniaoSiteSubServiceInterface;
use App\AgendamentoSala;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoSalaMail;
use Carbon\Carbon;

class SalaReuniaoSiteSubService implements SalaReuniaoSiteSubServiceInterface {

    public function verificaPodeAgendar($user)
    {
        if(!$user->podeAgendar())
            return [
                'message' => 'Já possui o limite de 4 agendamentos a finalizar no mês.',
                'class' => 'alert-danger'
            ];
    }

    public function save($dados, $user)
    {
        $dia = Carbon::createFromFormat('d/m/Y', $dados['dia'])->format('Y-m-d');
        $participantes = null;
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
            'tipo_sala' => $dados['tipo_sala'],
            'protocolo' => $protocolo,
        ]);

        $string = $user->nome.' (CPF / CNPJ: '.$user->cpf_cnpj.') *agendou* reserva da sala em *'.$agendamento->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia).' para '.$agendamento->tipo_sala.', no período ' .$agendamento->periodo;
        event(new ExternoEvent($string));

        Mail::to($user->email)->queue(new AgendamentoSalaMail($agendamento));
    }

    public function verificaPodeEditar($id, $user)
    {
        $agendamento = $user->agendamentosSalas()->where('id', $id)->firstOrFail();
        if($agendamento->podeEditarParticipantes())
            return ['agendamento' => $agendamento];
        return [
            'message' => 'Não é possível editar o agendamento.',
            'class' => 'alert-danger'
        ];
    }
}