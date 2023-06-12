<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AgendamentoSalaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($agendamento)
    {
        $this->body = "<strong>Sua reserva de sala foi agendada com sucesso!</strong>";
        $this->body  .= "<br>";
        $this->body  .= "Por favor, compareça ao escritório do CORE-SP com no mínimo 15 minutos de antecedência e com o número de protocolo em mãos.";
        $this->body  .= "<br><br>";
        $this->body  .= "<strong>Protocolo:</strong> " . $agendamento->protocolo;
        $this->body  .= "<br><br>";
        $this->body  .= "<strong>Detalhes do agendamento</strong><br>";
        $this->body  .= "Representante responsável: " . $agendamento->representante->nome . "<br>";
        $this->body  .= "CPF / CNPJ: " . $agendamento->representante->cpf_cnpj . "<br>";
        $this->body  .= "Sala: " . $agendamento->getTipoSala() . '<br>';
        $this->body  .= "Dia: " . onlyDate($agendamento->dia) . "<br>";
        $this->body  .= "Período: " . $agendamento->getPeriodo() . "<br>";
        $this->body  .= "Cidade: " . $agendamento->sala->regional->regional . "<br>";
        $this->body  .= "Endereço: " . $agendamento->sala->regional->endereco.", " . $agendamento->sala->regional->numero;
        $this->body  .= " - " . $agendamento->sala->regional->complemento . "<br>";
        if($agendamento->tipo_sala == 'reuniao')
        {
            $this->body  .= "Participantes: <br>";
            foreach($agendamento->getParticipantes() as $cpf => $nome)
                $this->body  .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CPF: " . formataCpfCnpj($cpf) . "&nbsp;&nbsp;|&nbsp;&nbsp;Nome: " . $nome . "<br>";
        }
        
    }

    public function build()
    {
        return $this->subject('Agendamento de uso de sala pelo site')
            ->view('emails.default');
    }
}
