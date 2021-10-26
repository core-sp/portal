<?php

namespace App\Mail;

use App\SolicitaCedula;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SolicitaCedulaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($cedula)
    {
        if($cedula->status == SolicitaCedula::STATUS_EM_ANDAMENTO) {
            $this->body = 'Bem vindo ao CORE-SP. Seu pedido de emissão da cédula profissional está em análise e será postado em até 10 dias após a aprovação.';
            $this->body .= '<br /><br />';
            $this->body .= '<strong>Código da solicitação:</strong> #'. $cedula->id;
            $this->body .= '<br /><br />';
            $this->body .= '<strong>Status:</strong> '. $cedula->status;
            $this->body .= '<br /><br />';
            $this->body .= 'Poderá acompanhar o andamento pela sua própria área restrita.';
            $this->body .= '<br /><br />';
            $this->body .= 'O CORE-SP agradece sua solicitação.';
        } else{
            $this->body = 'Falta o texto para a atualização';
            // $this->body .= '<br /><br />';
            // $this->body .= '<strong>Código da solicitação:</strong> #'. $cedula->id;
            // $this->body .= '<br /><br />';
            // $this->body .= '<strong>Status:</strong> '. $cedula->status;
            // if($cedula->status == SolicitaCedula::STATUS_RECUSADO) {
            //     $this->body .= '<br /><br />';
            //     $this->body .= '<strong>Justificativa:</strong> '. $cedula->justificativa;
            // }
        }
        $this->body .= '<br /><br />';
        $this->body .= 'Atenciosamente';
        $this->body .= '<br /><br />';
        $this->body .= 'Equipe CORE-SP.';
    }

    public function build()
    {
        return $this->subject('Solicitação de cédula no Portal CORE-SP')
            ->view('emails.default');
    }
}
