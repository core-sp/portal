<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CadastroUserExternoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    public $token;
    public $externo;

    public function __construct($token = null, $externo = null)
    {
        $this->token = $token;
        $this->externo = $externo;
    }

    private function criar()
    {
        $body = '<strong>Cadastro no Login Externo do Portal Core-SP realizado com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Você deve ativar sua conta em até 24h, caso contrário deve se recadastrar.';
        $body .= '<br /><br />';
        $body .= 'Para concluir o processo, basta clicar <a href="'. route('externo.verifica-email', $this->token) .'">NESTE LINK</a>.';

        return $body;
    }

    private function atualizar()
    {
        $body = '<strong>Alteração de Dados no Login Externo do Portal Core-SP realizada com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Você alterou algum ou alguns dados (nome, e-mail e/ou senha) após o logon no Portal do Core-SP às ' . organizaData($this->externo->fresh()->updated_at) . '.';
        $body .= '<br /><br />';

        return $body;
    }

    public function build()
    {
        $this->body = !isset($this->externo) ? $this->criar() : $this->atualizar();
        $titulo = isset($this->externo) ? 'Alteração de cadastro' : 'Cadastro';
        return $this->subject($titulo . ' no Login Externo do Portal Core-SP')
            ->view('emails.default');
    }
}
