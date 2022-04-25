<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InternoAgendamentoMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $body;
    public $user;
    public $agendados;
    public $subject;
    public $dia;

    public function __construct($user, $agendados, $subject)
    {
        $this->user = $user;
        $this->agendados = $agendados;
        $this->subject = $subject;
        $this->dia = onlyDate(date('Y-m-d'));
    }

    public function build()
    {
        $this->getTabela();
        return $this->subject('Agendamentos '.$this->subject.' (dia: '.$this->dia.')')
            ->view('emails.interno')
            ->with([
                'body' => $this->body,
            ]);
    }

    private function topBottomTabela($conteudo)
    {
        $body = '<h3><i>(Mensagem Programada)</i></h3>';

        $body.= $conteudo;

        $body .= '<p>';
        $body .= 'Por favor, acesse o <a href="https://core-sp.org.br/admin" target="_blank">painel de administrador</a> do Portal CORE-SP para mais informações.';
        $body .= '</p>';

        return $body;
    }

    private function conteudoComDados()
    {
        $body = '<p>Confira abaixo a lista de agendamentos solicitados pelo Portal CORE-SP hoje, <strong>'.$this->dia.':</strong></p>';
        $body .= '<table border="1" cellspacing="0" cellpadding="6">';
        $body .= '<thead>';
        $body .= '<tr>';
        $body .= '<th>Regional</th>';
        $body .= '<th>Horário</th>';
        $body .= '<th>Protocolo</th>';
        $body .= '<th>Nome</th>';
        $body .= '<th>CPF</th>';
        $body .= '<th>Serviço</th>';
        $body .= '</tr>';
        $body .= '</thead>';
        $body .= '<tbody>';

        foreach($this->agendados as $agendado) 
        {
            $body .= '<tr>';
            $body .= '<td>'.$agendado->regional.'</td>';
            $body .= '<td>'.$agendado->hora.'</td>';
            $body .= '<td>'.$agendado->protocolo.'</td>';
            $body .= '<td>'.$agendado->nome.'</td>';
            $body .= '<td>'.$agendado->cpf.'</td>';
            $body .= '<td>'.$agendado->tiposervico.'</td>';
            $body .= '</tr>';
        }

        $body .= '</tbody>';
        $body .= '</table>';

        return $body;
    }

    private function conteudoSemDados()
    {
        $body = '<p>';
        $body .= $this->agendados == 1 ? 'Existe <strong>1 atendimento agendado</strong> ' : 'Existem <strong>'.$this->agendados.' atendimentos agendados<strong> ';
        $body .= 'em '.$this->user->regional->regional.' hoje, dia <strong>'.$this->dia.'.</strong>';
        $body .= '</p><p>----------</p>';

        return $body;
    }

    private function getTabela()
    {
        if(in_array($this->user->idperfil, [8]))
            $body = $this->topBottomTabela($this->conteudoSemDados());
        elseif(in_array($this->user->idperfil, [1, 6, 12, 13, 21]))
            $body = $this->topBottomTabela($this->conteudoComDados());

        $this->body = $body;
    }
}
