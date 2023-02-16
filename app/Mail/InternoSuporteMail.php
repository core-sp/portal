<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\SuporteIp;

class InternoSuporteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($ip, $status)
    {
        $cor = $status == SuporteIp::BLOQUEADO ? 'red' : 'blue';

        $this->body = '[Rotina do Portal] - Bloqueio / Desbloqueio de IP.';
        $this->body .= '<br /><br />';
        $this->body .= '<strong>IP: </strong> '. $ip;
        $this->body .= '<br /><br />';
        $this->body .= '<strong>Status:</strong> <span style="color:'. $cor .';">'. $status .'</span>';
        $this->body .= '<br /><br />';
        $this->body .= 'Registrado nos logs.';
        $this->body .= '<br /><br />';
        $this->body .= 'Equipe de TI.';
    }

    public function build()
    {
        return $this->subject('[Rotina do Portal] - Bloqueio / Desbloqueio de IP')
            ->view('emails.default');
    }
}
