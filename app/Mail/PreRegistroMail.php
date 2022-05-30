<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PreRegistroMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($preRegistro)
    {
        $this->body = "<strong>Sua solicitação de registro foi realizada com sucesso!</strong>";
        $this->body  .= "<br>";
        // $this->body  .= "Por favor, compareça ao escritório do CORE-SP com no mínimo 15 minutos de antecedência e com o número de protocolo em mãos.";
        // $this->body  .= "<br><br>";
        // $this->body  .= "<strong>Protocolo:</strong> " . $agendamento->protocolo;
        // $this->body  .= "<br><br>";
        // $this->body  .= "<strong>Detalhes do agendamento</strong><br>";
        // $this->body  .= "Nome: " . $agendamento->nome . "<br>";
        // $this->body  .= "CPF: " . $agendamento->cpf . "<br>";
        // $this->body  .= "Dia: " . onlyDate($agendamento->dia) . "<br>";
        // $this->body  .= "Horário: " . $agendamento->hora . "<br>";
        // $this->body  .= "Cidade: " . $agendamento->regional->regional . "<br>";
        // $this->body  .= "Endereço: " . $agendamento->regional->endereco.", " . $agendamento->regional->numero;
        // $this->body  .= " - " . $agendamento->regional->complemento . "<br>";
        // $this->body  .= "Serviço: " . $agendamento->tiposervico . '<br>';
        // $this->body  .= "<br>";
        // $this->body  .= 'Antes de comparecer no CORE-SP, acesse o link a seguir e verifique os procedimentos (documentação e valores) que precisam ser apresentados no dia do agendamento - <a href="' . route('site.home') . '/servicos-atendimento-ao-rc"><u>Atendimento ao Representante Comercial</u></a>';
    }

    public function build()
    {
        return $this->subject('Solicitação de registro')
            ->view('emails.default');
    }
}
