<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Pagamento extends Model
{
    use SoftDeletes;

    protected $table = 'pagamentos';
    protected $guarded = [];
    protected $hidden = ['transacao_temp'];

    private static function cartoesImg($brand = null)
    {
        $cartoes = [
            'Visa' => '<img src="' . asset('img/visa.256x164.png') . '" width="40" alt="cartão visa"/>',
            'Mastercard' => '<img src="' . asset('img/mastercard.256x164.png') . '" width="40" alt="cartão mastercard"/>',
            'Elo' => '<img src="' . asset('img/elo.256x164.png') . '" width="40" alt="cartão elo"/>',
            'Amex' => '<img src="' . asset('img/amex.256x168.png') . '" width="40" alt="cartão amex"/>',
            'Hipercard' => '<img src="' . asset('img/hipercard.256x112.png') . '" width="45" alt="cartão hipercard"/>',
        ];

        if(isset($brand))
            return isset($cartoes[ucfirst($brand)]) ? $cartoes[ucfirst($brand)] : '';
        return $cartoes;
    }

    private static function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'Usuário',
            'ID Pagamento',
            'Cobrança',
            'Forma de pagamento',
            'Parcelas',
            'Status',
            'Gerenti',
            'Atualizado em',
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) 
        {
            $icone_gerenti = $resultado->isAutorizado() ? '<i class="fas fa-exclamation-circle"></i> <em>' : '<i class="fas fa-check text-success"></i> <em>';
            $texto_gerenti = $resultado->isAutorizado() ? 'Aguardando confirmação<br>do pagamento' : 'Enviado';
            $forma = $resultado->getForma() . ' ' . $resultado->getBandeiraImg();
            $forma .= $resultado->isCombinado() ? '<br><small><em><strong>Tag:</strong> ' . $resultado->payment_tag . ' | <strong>Total parcial:</strong> ' . $resultado->getValorParcial() . '</em></small>' : '';
            $combinado = $resultado->isCombinado() ? 
            '<br><small><em><strong>ID Combinado:</strong> ' . substr_replace($resultado->combined_id, '**********', 9, strlen($resultado->combined_id)) . '</em></small>' : 
            '';
            $conteudo = [
                $resultado->id,
                $resultado->getUser()->nome.'<br><small><em>'.formataCpfCnpj($resultado->getUser()->cpf_cnpj).'</em></small>',
                substr_replace($resultado->payment_id, '**********', 9, strlen($resultado->payment_id)) . $combinado,
                'ID: ' . $resultado->cobranca_id.'<br><small><em><strong>Total:</strong> '.$resultado->getValor().'</em></small>',
                $forma,
                $resultado->getParcelas(),
                '<small>' . $resultado->getStatusLabel() . '</small>',
                $resultado->gerenti_ok ? $icone_gerenti . $texto_gerenti . '</em>' : '<i class="fas fa-times text-danger"></i> <em>Aguardando envio</em>',
                formataData($resultado->updated_at),
            ];
            array_push($contents, $conteudo);
        }
        $classes = [
            'table',
            'table-hover'
        ];

        $aviso = '<p><strong>**** EM DESENVOLVIMENTO ****</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>**** EM DESENVOLVIMENTO ****</strong></p><br>';
        $legenda = '<p><small><em><strong>Legenda:</strong></em>&nbsp;&nbsp;';
        foreach(self::cartoesImg() as $brand => $img)
            $legenda .= $brand . ' ' . $img . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $legenda .= '</small>|<span class="ml-2"><strong>Pagamento aprovado</strong> = Aprovado ou Confirmado</span></p><hr />';
        $tabela = $aviso . $legenda . montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public static function listar($resultados)
    {
        $variaveis = [
            'singular' => 'pagamento',
            'singulariza' => 'o pagamento',
            'plural' => 'pagamentos',
            'pluraliza' => 'pagamentos',
        ];

        return [
            'resultados' => $resultados, 
            'tabela' => self::tabelaCompleta($resultados), 
            'variaveis' => (object) $variaveis
        ];
    }

    public static function getFirst($pagamentos)
    {
    	return $pagamentos instanceof Pagamento ? $pagamentos : $pagamentos->first();
    }

    public static function getCollection($pagamentos)
    {
    	return $pagamentos instanceof Pagamento ? collect([$pagamentos]) : $pagamentos;
    }

    public static function logAposTransacaoByUser($user, $pagamento, $status, $requestAll, $sessionAll, $cacheAll)
    {
        $string = '';
    	if($status != 'CANCELED')
        {
            $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou pagamento da cobrança ' . $pagamento->cobranca_id . ' do tipo *' . $pagamento->forma . '* com a ';
            $string .= !$pagamento->isCombinado() ? 'payment_id: ' . $pagamento->payment_id : 'combined_id: '. $pagamento->combined_id;
            $string .= '. [Dados Request:' . json_encode($requestAll) . ']; [Dados Session:' . json_encode($sessionAll) . ']; [Dados Cache:' . json_encode($cacheAll) . ']';
        }else{
            $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou o cancelamento do pagamento da cobrança ' . $pagamento->cobranca_id . ' do tipo *' . $pagamento->forma . '* com a ';
            $string .= !$pagamento->isCombinado() ? 'payment_id: ' . $pagamento->payment_id : 'combined_id: ' . $pagamento->combined_id;
        }

        return $string;
    }

    public static function logAposTransacaoByNotification($user, $pagamento, $status, $errorCode, $descricao, $via_sistema)
    {
        $string = '';
    	if(in_array($status, ['APPROVED', 'CONFIRMED']))
        {
            $texto = $pagamento->isCombinado() ? 'combined_id: ' . $pagamento->combined_id : 'payment_id: ' . $pagamento->payment_id;
            $string = $via_sistema . 'ID: ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou pagamento da cobrança ' . $pagamento->cobranca_id;
            $string .= ' do tipo *' . $pagamento->forma . '*, com a ' . $texto . '.';
        }elseif($status == 'CANCELED'){
            $string = $via_sistema . 'ID: ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou a atualização de status de pagamento da cobrança ' . $pagamento->cobranca_id . ' do tipo *combined*';
            $string .= ' com a combined_id: ' . $pagamento->combined_id . ' após passar do prazo de cancelamento via Portal com a Getnet (7 dias) e não foi confirmado.';
        }elseif(in_array($status, ['ERROR', 'DENIED']))
            $string = $via_sistema . 'ID: ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') tentou realizar um pagamento. Detalhes da Getnet: error code - [' . $errorCode . '], description details - [' . $descricao . '].';
            
        return $string;
    }

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }

    public function aprovado()
    {
        return ($this->status == 'APPROVED') || ($this->status == 'CONFIRMED');
    }

    public function cancelado()
    {
        return $this->status == 'CANCELED';
    }

    public function getForma()
    {
        switch($this->forma)
        {
            case 'debit':
                return 'Débito';
            case 'credit':
                return 'Crédito';
            case 'combined':
                return 'Crédito em dois cartões';
        }
    }

    public function getValor()
    {
        return 'R$ ' . $this->total;
    }

    public function getValorParcial()
    {
        return $this->isCombinado() ? 'R$ ' . $this->total_combined : '';
    }

    public function getStatus()
    {
        switch($this->status)
        {
            case 'APPROVED':
                return 'Aprovado';
            case 'AUTHORIZED':
                return 'Autorizado';
            case 'CONFIRMED':
                return 'Confirmado';
            case 'CANCELED':
                return 'Cancelado';
            case 'DENIED':
                return 'Negado';
            case 'ERROR':
                return 'Erro';
            default:
                return '';
        }
    }

    public function getStatusLabel()
    {
        switch($this->status)
        {
            case 'APPROVED':
                return '<span class="border rounded bg-success font-weight-bold p-1">Aprovado</span>';
            case 'AUTHORIZED':
                return '<span class="border rounded bg-warning font-weight-bold p-1">Autorizado</span>';
            case 'CONFIRMED':
                return '<span class="border rounded bg-success font-weight-bold p-1">Confirmado</span>';
            case 'CANCELED':
                return '<span class="border rounded bg-danger font-weight-bold p-1">Cancelado</span>';
            case 'DENIED':
                return '<span class="border rounded bg-danger font-weight-bold p-1">Negado</span>';
            case 'ERROR':
                return '<span class="border rounded bg-danger font-weight-bold p-1">Erro</span>';
            default:
                return '';
        }
    }

    public function getStatusLabelMail()
    {
        switch($this->status)
        {
            case 'APPROVED':
                return '<span style="color:green;"><strong>Aprovado</strong></span>';
            case 'AUTHORIZED':
                return '<span style="color:green;"><strong>Autorizado</strong></span>';
            case 'CONFIRMED':
                return '<span style="color:green;"><strong>Confirmado</strong></span>';
            case 'CANCELED':
                return '<span style="color:red;"><strong>Cancelado</strong></span>';
            case 'DENIED':
                return '<span style="color:red;"><strong>Negado</strong></span>';
            case 'ERROR':
                return '<span style="color:red;"><strong>Erro</strong></span>';
            default:
                return '';
        }
    }

    public function getParcelas()
    {
        return $this->parcelas == '1' ? 'à vista' : $this->parcelas .'x';
    }

    public function getTipoParcelas()
    {
        switch($this->tipo_parcelas)
        {
            case 'INSTALL_NO_INTEREST':
                return 'sem juros';
            case 'INSTALL_WITH_INTEREST':
                return 'com juros';
            case 'FULL':
            default:
                return '';
        }
    }

    public function getBandeiraTxt()
    {
        switch($this->bandeira)
        {
            case 'mastercard':
                return 'Mastercard';
            case 'visa':
                return 'Visa';
            case 'amex':
                return 'Amex';
            case 'elo':
                return 'Elo';
            case 'hipercard':
                return 'Hipercard';
            default:
                return '';
        }
    }

    public function getBandeiraImg()
    {
        return self::cartoesImg($this->bandeira);
    }

    public function canCancel()
    {
        if($this->isDebit())
            return false;
        if($this->cancelado())
            return false;

        $hoje = Carbon::now('UTC')->format('Y-m-d');
        $formato = strpos($this->authorized_at, '.') !== false ? 'Y-m-d\TH:i:s.uZ' : 'Y-m-d\TH:i:sZ';
        $authorized_at = Carbon::createFromFormat($formato, $this->authorized_at);

        return $this->isAutorizado() ? $authorized_at->addDays(7)->format('Y-m-d') >= $hoje : $authorized_at->format('Y-m-d') == $hoje;
    }

    public function getIdPagamento()
    {
        return $this->isCombinado() ? $this->combined_id : $this->payment_id;
    }

    public function getForma3DS()
    {
        switch($this->forma)
        {
            case 'debit':
                return 'debit_3ds';
            case 'credit':
                return $this->is_3ds ? 'credit_3ds' : 'credit';
            case 'combined':
                return 'combined';
        }
    }

    public function getUser()
    {
        if(isset($this->idrepresentante))
            return $this->representante;
    }

    public function isDebit()
    {
        return $this->forma == 'debit';
    }

    public function isCombinado()
    {
        return $this->forma == 'combined';
    }

    public function isAutorizado()
    {
        return $this->status == 'AUTHORIZED';
    }

    public function updateCancelamento($status, $message)
    {
        return $this->update([
            'status' => is_array($status) ? $status[0] : $status,
            'canceled_at' => $message['dt'],
            'transacao_temp' => null
        ]);
    }

    public function updateAposErroGerenti($transacao)
    {
        return $this->update([
            'gerenti_ok' => false,
            'transacao_temp' => json_encode($transacao, JSON_FORCE_OBJECT),
        ]);
    }

    public function updateAposSucessoGerenti()
    {
        return $this->update([
            'gerenti_ok' => true,
            'transacao_temp' => null,
        ]);
    }
}
