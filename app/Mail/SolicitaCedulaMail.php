<?php

namespace App\Mail;

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
        if($cedula->cedulaEmAndamento()) {
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
            $this->body = 'Atualização do seu pedido de emissão de cédula profissional';
            $this->body .= '<br /><br />';
            $this->body .= '<strong>Código da solicitação:</strong> #'. $cedula->id;
            $this->body .= '<br /><br />';

            $cor = $cedula->cedulaRecusada() ? 'red' : 'blue';

            $this->body .= '<strong>Status:</strong> <span style="color:'. $cor .';">'. $cedula->status .'</span>';

            if($cedula->contemTipoDigital() && $cedula->cedulaAceita()){
                $this->body .= '<br /><br />';
                $this->body .= '<strong>Pedido de Cédula Digital aceito. Em breve, receberá um e-mail com instruções de cadastro no aplicativo. ';
                $this->body .= '<br>Certifique que o e-mail cadastrado na Área Restrita do Representante está ativo.</strong>';
            }

            if($cedula->cedulaRecusada()) {
                $this->body .= '<br /><br />';
                $this->body .= '<strong>Motivo:</strong> '. $cedula->justificativa;
                $this->body .= '<br /><br />';
                $this->body .= 'Diante disso, deverá cumprir com a exigência acima e posteriormente ingressar com o novo pedido';
                $this->body .= ' de emissão de cédula na Área Restrita do Representante Comercial no Portal do CORE-SP.';
            }
        }
        $this->body .= '<br /><br />';
        $this->body .= 'Atenciosamente';
        $this->body .= '<br /><br />';
        $this->body .= 'Equipe de Atendimento.';
    }

    public function build()
    {
        return $this->subject('Pedido de emissão de cédula profissional no Portal CORE-SP')
            ->view('emails.default');
    }
}
