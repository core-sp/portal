<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertidaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    private $pdf;

    public function __construct($pdf)
    {
        $this->pdf = base64_encode($pdf);

        $this->body = "<strong>Sua certidão eletrônica segue em anexo.</strong>";
    }

    public function build()
    {
        return $this->subject('Certidão Eletrônica')
            ->view('emails.default')
            ->attachData(base64_decode($this->pdf), 'certidao.pdf', [
                'mime' => 'application/pdf',
            ]);;
    }
}
