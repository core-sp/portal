<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SolicitacaoAlteracaoEnderecoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($id)
    {
        $this->body = 'Nova solicitação de alteração de endereço no Portal Core-SP.';
        $this->body .= '<br /><br />';
        $this->body .= '<strong>Código da solicitação:</strong> #'. $id;
        $this->body .= '<br /><br />';
        $this->body .= 'Para verifica-la, acesse o <a href="' . route('site.home') . '/admin">painel de administração</a> do Portal Core-SP.';
    }

    public function build()
    {
        return $this->subject('Nova solicitação de alteração de endereço')
            ->view('emails.default');
    }
}
