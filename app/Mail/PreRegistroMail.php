<?php

namespace App\Mail;

use App\PreRegistro;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PreRegistroMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    private function analiseInicial()
    {
        $this->body = "<strong>Sua solicitação de registro foi realizada com sucesso!</strong>";
        $this->body .= "<br>";
        $this->body .= "<br>";
        $this->body .= "Acusamos o recebimento do formulário para registro com os documentos encaminhados. ";
        $this->body .= "<br>";
        $this->body .= "Solicitamos aguardar a conferência das informações e validação dos documentos pelo setor de atendimento. ";
        $this->body .= "<br>";
        $this->body .= "Em breve aparecerá o status da validação de cada item necessário.";
    }

    private function aguardandoCorrecao()
    {
        $this->body = "<strong>Sua solicitação de registro foi analisada!</strong>";
        $this->body .= "<br>";
        $this->body .= "<br>";
        $this->body .= "Aguardamos a correção dos itens para prosseguimento do processo de registro.";
    }

    private function analiseCorrecao()
    {
        $this->body = "<strong>Sua solicitação de registro foi enviada para análise!</strong>";
        $this->body .= "<br>";
        $this->body .= "<br>";
        $this->body .= "Acusamos o recebimento da correção dos itens. ";
        $this->body .= "<br>";
        $this->body .= "Solicitamos aguardar a conferência das informações e validação pelo setor de atendimento. ";
        $this->body .= "<br>";
        $this->body .= "Em breve aparecerá o status da validação de cada item necessário.";
    }

    private function aprovado()
    {
        $this->body = "<strong>Sua solicitação de registro foi aprovada!</strong>";
        $this->body .= "<br>";
        $this->body .= "<br>";
        $this->body .= "O pré-cadastro do registro foi aprovado.";
        $this->body .= "<br>";
        $this->body .= "Em breve, receberá as vias para pagamento.";
    }

    private function negado($preRegistro)
    {
        $this->body = "<strong>Sua solicitação de registro foi negada!</strong>";
        $this->body .= "<br>";
        $this->body .= "<br>";
        $this->body .= "O pré-cadastro do registro foi negado, conforme motivo abaixo indicado:";
        $this->body .= "<br>";
        $this->body .= "<strong>Justificativa: </strong>" . $preRegistro->getJustificativaNegado();
        $this->body .= "<br><br>";
        $this->body .= "Poderá ingressar com novo pedido de registro, após o cumprimento da exigência indicada acima.";
    }

    public function __construct($preRegistro)
    {
        switch ($preRegistro->status) {
            case PreRegistro::STATUS_ANALISE_INICIAL:
                $this->analiseInicial();
                break;
            case PreRegistro::STATUS_CORRECAO:
                $this->aguardandoCorrecao();
                break;
            case PreRegistro::STATUS_ANALISE_CORRECAO:
                $this->analiseCorrecao();
                break;
            case PreRegistro::STATUS_APROVADO:
                $this->aprovado();
                break;
            case PreRegistro::STATUS_NEGADO:
                $this->negado($preRegistro);
                break;
        }

        $this->body .= '<br><br /><br />';
        $this->body .= 'Atenciosamente';
        $this->body .= '<br /><br />';
        $this->body .= 'Equipe de Atendimento.';
    }

    public function build()
    {
        return $this->subject('Solicitação de registro')
            ->view('emails.default');
    }
}
