<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnunciarVagaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($id)
    {
        $this->body = 'Nova solicitação de inclusão de oportunidade no Balcão de Oportunidades do Core-SP.';
        $this->body .= '<br><br>';
        $this->body .= '<strong>Código da Oportunidade:</strong> #' . $id;
        $this->body .= '<br><br>';
        $this->body .= 'Favor acessar o <a href="'. route('site.home') .'/admin/bdo/editar/'. $id .'">painel de administrador</a> do Core-SP para validar as informações.';
    }

    public function build()
    {
        return $this->subject('Solicitação de inclusão no Balcão de Oportunidades')
            ->view('emails.bdo');
    }
}
