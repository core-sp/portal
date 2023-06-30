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
            case 'aceito':
            case 'recusa':
                return 'Atualização do não comparecimento de uso de sala pelo site';
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
                $texto .= "<br>Por favor, compareça ao escritório do CORE-SP com o número de protocolo em mãos e documento de identificação com foto.";
                break;
            case 'justificar':
                $texto = "<strong>Justificativa do não comparecimento da reserva de sala.</strong>";
                break;
            case 'aceito':
                $texto = "<strong>Justificativa do não comparecimento da reserva de sala foi aceita.</strong>";
                break;
            case 'recusa':
                $texto = "<strong>Justificativa do não comparecimento da reserva de sala foi recusada.</strong>";
                $texto .= "<br><strong>Devido a recusa da justificativa, será suspenso por 30 dias para criar novos agendamentos.</strong>";
                break;
            default:
                $texto = "<strong>Sua reserva de sala foi agendada com sucesso!</strong>";
                $texto .= "<br>Por favor, compareça ao escritório do CORE-SP com o número de protocolo em mãos e documento de identificação com foto.";
        }

        return $texto;
    }

    public function __construct($agendamento, $acao = 'agendar')
    {
        $this->acao = $acao;
        $this->body = $this->getTitulo();
        
        if($acao == 'recusa')
            $this->body .= '<br><br><span style="color: red;"><strong>Motivo da recusa</strong></span>: ' . $agendamento->justificativa_admin . "<br>";
            
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
        if($agendamento->isReuniao())
        {
            $this->body .= "Participantes: <br>";
            foreach($agendamento->getParticipantes() as $cpf => $nome)
                $this->body .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CPF: " . formataCpfCnpj($cpf) . "&nbsp;&nbsp;|&nbsp;&nbsp;Nome: " . $nome . "<br>";
        }

        if($acao == 'justificar')
            $this->body .= '<br><strong>Justificativa</strong>: ' . $agendamento->justificativa . "<br>";
        
        if(!in_array($acao, ['justificar', 'aceito', 'recusa']))
            $this->body .= "<br><strong>Em caso de não comparecimento, deve justifcar a partir de ".onlyDate($agendamento->dia)." até ".$agendamento->getDataLimiteJustificar().", caso contrário será suspenso por 30 dias.</strong><br>";

        $this->body .= "<br><strong>Conforme a Resolução Nº 01/2023:</strong><br>";
        $this->body .= "<strong>6.</strong> Os usuários das salas de reunião deverão respeitar regras básicas de convivência, como, por
        exemplo, manter o silêncio e conservar o local de trabalho limpo e organizado.<br>";
        $this->body .= "<strong>7.</strong> É vedado ao usuário:<br>";
        $this->body .= "<strong>&nbsp;&nbsp;&nbsp;&nbsp;a)</strong> Fazer lanches ou refeições no espaço;<br>";
        $this->body .= "<strong>&nbsp;&nbsp;&nbsp;&nbsp;b)</strong> Usar as salas de reunião para fins de registro de endereço comercial e/ou correspondência;<br>";
        $this->body .= "<strong>&nbsp;&nbsp;&nbsp;&nbsp;c)</strong> Danificar qualquer material ou equipamento que se encontre na sala de reunião.<br>";
        $this->body .= "<strong>8.</strong> A prática de condutas vedadas implicará na impossibilidade de uso das salas de reunião por período a ser ";
        $this->body .= "determinado pela Presidência do Core-SP, sem prejuízo do ressarcimento de eventuais danos.<br>";
    }

    public function build()
    {
        $assunto = $this->getAssunto();
        
        return $this->subject($assunto)->view('emails.default');
    }
}
