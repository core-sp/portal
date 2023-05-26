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
    public $tipo;
    public $token;
    public $externo;
    public $criadoContabil;

    public function __construct($tipo, $token = null, $externo = null, $criadoContabil = false)
    {
        $this->tipo = $tipo;
        $this->token = $token;
        $this->externo = $externo;
        $this->criadoContabil = $criadoContabil;
    }

    private function criar()
    {
        $body = '<strong>Cadastro no Login Externo do Portal Core-SP realizado com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Você deve ativar sua conta em até 24h, caso contrário deve se recadastrar.';
        $body .= '<br /><br />';
        $body .= 'Para concluir o processo, basta clicar <a href="'. route('externo.verifica-email', [$this->tipo, $this->token]) .'">NESTE LINK</a>.';

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

    private function criadoContabil()
    {
        $body = '<strong>Cadastro no Login Externo do Portal Core-SP foi previamente criado pela contabilidade '.$this->externo->nome.'!</strong>';
        $body .= '<br /><br />';
        $body .= 'A contabilidade <strong>'.$this->externo->nome.'</strong> com o CNPJ <strong>'.formataCpfCnpj($this->externo->cnpj).'</strong> iniciou o processo de solicitação de registro no Portal do Core-SP';
        $body .= ' com o seu CPF / CNPJ, nome e e-mail.';
        $body .= '<br /><br />';
        $body .= 'Para acompanhar a solicitação ou caso queira remover a permissão da contabilidade ou trocar de contabilidade, você deve confirmar o cadastro';
        $body .= ' no Login Externo <a href="'. route('externo.cadastro') .'">NESTE LINK</a> e, após verificação do e-mail, pode realizar o login e acessar a aba "Solicitar Registro".';

        return $body;
    }

    public function build()
    {
        if(($this->tipo == 'contabil') && ($this->criadoContabil)){
            $this->body = $this->criadoContabil();
            $titulo = 'Cadastro previamente criado';
        }else{
            $this->body = !isset($this->externo) ? $this->criar() : $this->atualizar();
            $titulo = isset($this->externo) ? 'Alteração de cadastro' : 'Cadastro';
        }
        
        return $this->subject($titulo . ' no Login Externo do Portal Core-SP')
            ->view('emails.default');
    }
}
