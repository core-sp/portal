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

    public function __construct($pagamentos)
    {
        $pagamento = null;
        if($pagamentos instanceof Pagamento)
        {
            $pagamento = $pagamentos;
            $pagamentos = collect([$pagamentos]);
        }else
            $pagamento = $pagamentos->first();

        $this->textoAssunto($pagamento);

        $detalhes = 'O ' . $this->status . ' do boleto ' . $pagamento->boleto_id . ' foi realizado!';
        $detalhes .= '<br /><br />';

        foreach($pagamentos as $key => $pag)
        {
            $detalhes .= '<strong>Cartão ' . ++$key . ':</strong> ';
            $detalhes .= '<br /><br />';
            $detalhes .= '<strong>Status do pagamento:</strong> '. $pag->getStatusLabelMail();
            $detalhes .= '<br /><br />';
            $detalhes .= '<strong>Forma de pagamento:</strong> '. $pag->getForma();
            $detalhes .= '<br /><br />';
            $detalhes .= '<strong>Parcelas:</strong> '. $pag->getParcelas() . ' '. $pag->getTipoParcelas();
            $detalhes .= '<br /><br /><hr />';
        }

        if($pagamento->aprovado()) {
            $link = route('pagamento.cancelar.view', ['boleto' => $pagamento->boleto_id, 'pagamento' => $pagamento->getIdPagamento()]);

            $this->body = $detalhes;
            $this->body .= '<strong>Caso não reconheça esse pagamento, cancele pelo <a href="' . $link . '">link de cancelamento</a>, na área restrita do ';
            $this->body .= $pagamento->getUser()::NAME_AREA_RESTRITA . '</strong>';
            $this->body .= '<br /><br />';
            $this->body .= '<strong>* Cancelamento somente no mesmo dia do pagamento realizado.</strong>';
            $this->body .= '<br /><br />';
        }
        if($pagamento->cancelado() || !$pagamento->aprovado())
            $this->body = $detalhes;

        $this->body .= '<br />';
        $this->body .= 'Atenciosamente';
        $this->body .= '<br /><br />';
        $this->body .= 'Equipe de Atendimento.';
    }

    private function textoAssunto($pagamento)
    {
        if($pagamento->cancelado())
            $this->status = 'Cancelamento do pagamento';
        elseif($pagamento->aprovado()) 
            $this->status = 'Pagamento';
        else
            $this->status = 'Alteração do status do pagamento';
    }

    public function build()
    {
        return $this->subject($this->status . ' on-line de boleto no Portal CORE-SP')
            ->view('emails.default');
    }
}
