<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BeneficiosMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    private $acao;

    public function __construct($inscricao)
    {
        $cor = $inscricao->trashed() ? 'style="color: red;"' : 'style="color: blue;"';
        $this->acao = $inscricao->trashed() ? 'remoção' : 'inclusão';

        $this->body = 'Nova solicitação de ' . $this->acao . ' de inscrição no benefício:';
        $this->body .= '<br><br>';
        $this->body .= '<strong>Ação:</strong> <span ' . $cor . '>'. ucfirst($this->acao) . '</span>';
        $this->body .= '<br><br>';
        $this->body .= '<strong>Benefício:</strong> ' . $inscricao->beneficio;
        $this->body .= '<br><br>';
        $this->body .= '<strong>Nome do Representante:</strong> ' . $inscricao->representante->nome;
        $this->body .= '<br><br>';
        $this->body .= '<strong>Registro do Representante:</strong> ' . $inscricao->representante->registro_core;
        $this->body .= '<br><br>';
        $this->body .= '<strong>CPF / CNPJ do Representante:</strong> ' . $inscricao->representante->cpf_cnpj;
        $this->body .= '<br><br>';
        $this->body .= '<strong>E-mail no Portal do Representante:</strong> ' . $inscricao->representante->email;
        $this->body .= '<br><br>';
        $this->body .= '<strong>Data da requisição:</strong> ' . formataData($inscricao->updated_at);
        $this->body .= '<br><br>';
        $this->body .= '<strong>ID da requisição:</strong> #' . $inscricao->id;
    }

    public function build()
    {
        return $this->subject('Solicitação de ' . $this->acao . ' de inscrição no Programa de Benefício')
            ->view('emails.bdo');
    }
}
