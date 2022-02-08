<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InternoSolicitaCedulaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    public $regional;
    public $dia;

    public function __construct($body, $regional, $dia)
    {
        $this->body = $body;
        $this->regional = $regional;
        $this->dia = $dia;
    }

    public function build()
    {
        return $this->subject('Solicitações de Cédulas '.$this->regional.' (dia: '.$this->dia.')')
            ->view('emails.interno');
    }
}
