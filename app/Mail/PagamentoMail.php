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
        $pagamentos = $pagamentos->fresh();
        $pagamento = Pagamento::getFirst($pagamentos);
        $pagamentos = Pagamento::getCollection($pagamentos);

        $this->textoAssunto($pagamento);

        $detalhes = 'O ' . $this->status . ' da cobrança ' . $pagamento->cobranca_id . ' foi realizado!';
        $detalhes .= '<br /><br />';
        $detalhes .= '<strong>Valor total:</strong> '. $pagamento->getValor();
        $detalhes .= '<br /><br /><hr />';

        foreach($pagamentos as $key => $pag)
        {
            $detalhes .= '<strong>Cartão ' . ++$key . ':</strong> ';
            $detalhes .= '<br /><br />';
            $detalhes .= '<strong>Valor total:</strong> '. $pag->getValorParcial();
            $detalhes .= '<br /><br />';
            $detalhes .= '<strong>Status do pagamento:</strong> '. $pag->getStatusLabelMail();
            $detalhes .= '<br /><br />';
            $detalhes .= '<strong>Forma de pagamento:</strong> '. $pag->getForma();
            $detalhes .= '<br /><br />';
            $detalhes .= '<strong>Parcelas:</strong> '. $pag->getParcelas() . ' '. $pag->getTipoParcelas();
            $detalhes .= '<br /><br />';
            $detalhes .= '<strong>Bandeira:</strong> '. $pag->getBandeiraTxt();
            $detalhes .= '<br /><br /><hr />';
        }

        if($pagamento->aprovado()) {
            $link = route('pagamento.cancelar.view', ['cobranca' => $pagamento->cobranca_id, 'pagamento' => $pagamento->getIdPagamento()]);
            $this->body = $detalhes;

            if(!$pagamento->isDebit())
            {
                $texto = $pagamento->isCombinado() ? 'pode ser feito em até 7 dias a partir do' : 'somente no mesmo';
                $this->body .= '<strong>Caso não reconheça esse pagamento, cancele pelo <a href="' . $link . '">link de cancelamento</a>, na área restrita do ';
                $this->body .= $pagamento->getUser()::NAME_AREA_RESTRITA . '</strong>';
                $this->body .= '<br /><br />';
                $this->body .= '<span style="color:red;"><strong>* Cancelamento ' . $texto . ' dia do pagamento realizado.</strong></span>';
                $this->body .= '<br /><br />';
            }
        }
        if(!$pagamento->aprovado())
            $this->body = $detalhes;

        $this->body .= '<br />';
        $this->body .= 'Atenciosamente';
        $this->body .= '<br /><br />';
        $this->body .= 'Portal do Core-SP.';
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
        return $this->subject($this->status . ' on-line de cobrança no Portal CORE-SP')
            ->view('emails.default');
    }
}
