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
    public $acao;

    private function getAssunto()
    {
        switch ($this->acao) {
            case 'editar':
                return 'Alteração do agendamento de uso de sala pelo site';
            case 'justificar':
                return 'Justificativa do não comparecimento de uso de sala pelo site';
            default:
                return 'Agendamento de uso de sala pelo site';
        }
    }

    private function getTitulo()
    {
        $texto = '';
        switch ($this->acao) {
            case 'editar':
                $texto = "<strong>Os participantes do agendamento da reserva de sala foram alterados com sucesso!</strong>";
                $texto .= "<br>Por favor, compareça ao escritório do CORE-SP com no mínimo 15 minutos de antecedência e com o número de protocolo em mãos.";
                break;
            case 'justificar':
                $texto = "<strong>Justificativa do não comparecimento da reserva de sala.</strong>";
                break;
            default:
                $texto = "<strong>Sua reserva de sala foi agendada com sucesso!</strong>";
                $texto .= "<br>Por favor, compareça ao escritório do CORE-SP com no mínimo 15 minutos de antecedência e com o número de protocolo em mãos.";
        }

        return $texto;
    }

    public function __construct($agendamento, $acao = 'agendar')
    {
        $this->acao = $acao;
        $this->body = $this->getTitulo();
        
        $this->body .= "<br><br>";
        $this->body .= "<strong>Protocolo:</strong> " . $agendamento->protocolo;
        $this->body .= "<br><br>";
        $this->body .= "<strong>Detalhes do agendamento</strong><br>";
        $this->body .= "Representante responsável: " . $agendamento->representante->nome . "<br>";
        $this->body .= "CPF / CNPJ: " . $agendamento->representante->cpf_cnpj . "<br>";
        $this->body .= "Sala: " . $agendamento->getTipoSala() . '<br>';
        $this->body .= "Dia: " . onlyDate($agendamento->dia) . "<br>";
        $this->body .= "Período: " . $agendamento->getPeriodo() . "<br>";
        $this->body .= "Cidade: " . $agendamento->sala->regional->regional . "<br>";
        $this->body .= "Endereço: " . $agendamento->sala->regional->endereco.", " . $agendamento->sala->regional->numero;
        $this->body .= " - " . $agendamento->sala->regional->complemento . "<br>";
        if($agendamento->tipo_sala == 'reuniao')
        {
            $this->body .= "Participantes: <br>";
            foreach($agendamento->getParticipantes() as $cpf => $nome)
                $this->body .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CPF: " . formataCpfCnpj($cpf) . "&nbsp;&nbsp;|&nbsp;&nbsp;Nome: " . $nome . "<br>";
        }

        if($acao == 'justificar')
            $this->body .= '<br><strong>Justificativa</strong>: ' . $agendamento->justificativa . "<br>";
    }

    public function build()
    {
        $assunto = $this->getAssunto();
        
        return $this->subject($assunto)->view('emails.default');
    }
}
