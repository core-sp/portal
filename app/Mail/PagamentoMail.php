<?php

namespace App\Mail;

use App\Pagamento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PagamentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    private $status;

    public function __construct($pagamento)
    {
        $this->status = $pagamento->status == 'CANCELED' ? 'Cancelamento do pagamento' : 'Pagamento';
        $detalhes = 'O ' . $this->status . ' do boleto ' . $pagamento->boleto_id . ' foi realizado!';
        $detalhes .= '<br /><br />';
        $detalhes .= '<strong>Status do pagamento:</strong> '. $pagamento->getStatus();
        $detalhes .= '<br /><br />';
        $detalhes .= '<strong>Forma de pagamento:</strong> '. $pagamento->getForma();
        $detalhes .= '<br /><br />';
        $detalhes .= '<strong>Parcelas:</strong> '. $pagamento->getParcelas();
        $detalhes .= '<br /><br />';

        if($pagamento->aprovado()) {
            $link = route('representante.cancelar.pagamento.cartao.view', ['boleto' => $pagamento->boleto_id, 'pagamento' => $pagamento->getIdPagamento()]);

            $this->body = $detalhes;
            $this->body .= '<strong>Caso não reconheça esse pagamento, cancele pelo <a href="' . $link . '">link de cancelamento</a>, na área restrita do ';
            $this->body .= isset($pagamento->representante) ? 'representante.' : '' . '</strong>';
            $this->body .= '<br /><br />';
            $this->body .= '<strong>* Cancelamento somente no mesmo dia do pagamento realizado.</strong>';
            $this->body .= '<br /><br />';
        }
        if($pagamento->cancelado())
            $this->body = $detalhes;

        $this->body .= '<br />';
        $this->body .= 'Atenciosamente';
        $this->body .= '<br /><br />';
        $this->body .= 'Equipe de Atendimento.';
    }

    public function build()
    {
        return $this->subject($this->status . ' on-line de boleto no Portal CORE-SP')
            ->view('emails.default');
    }
}
