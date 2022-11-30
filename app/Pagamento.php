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

    private static function cartoesImg($brand = null)
    {
        $cartoes = [
            'Visa' => '<img src="' . asset('img/visa.256x164.png') . '" width="40" height="26" alt="cartão visa"/>',
            'Mastercard' => '<img src="' . asset('img/mastercard.256x164.png') . '" width="40" height="26" alt="cartão mastercard"/>',
            'Elo' => '<img src="' . asset('img/elo.256x164.png') . '" width="40" height="26" alt="cartão elo"/>',
            'Amex' => '<img src="' . asset('img/amex.256x168.png') . '" width="40" height="27" alt="cartão amex"/>',
            'Hipercard' => '<img src="' . asset('img/hipercard.256x112.png') . '" width="40" height="16" alt="cartão hipercard"/>',
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
            'Boleto',
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
            $forma = $resultado->getForma() . ' ' . $resultado->getBandeiraImg();
            $forma .= isset($resultado->combined_id) ? '<br><small><em><strong>Tag:</strong> ' . $resultado->payment_tag . '</em></small>' : '';
            $combinado = isset($resultado->combined_id) ? 
            '<br><small><em><strong>ID Combinado:</strong> ' . substr_replace($resultado->combined_id, '**********', 9, strlen($resultado->combined_id)) . '</em></small>' : 
            '';
            $conteudo = [
                $resultado->id,
                $resultado->getUser()->nome.'<br><small><em>'.formataCpfCnpj($resultado->getUser()->cpf_cnpj).'</em></small>',
                substr_replace($resultado->payment_id, '**********', 9, strlen($resultado->payment_id)) . $combinado,
                $resultado->boleto_id,
                $forma,
                $resultado->getParcelas(),
                '<small>' . $resultado->getStatusLabel() . '</small>',
                $resultado->gerenti_ok ? '<i class="fas fa-check text-success"></i> <em>Enviado</em>' : '<i class="fas fa-times text-danger"></i> <em>Aguardando envio</em>',
                formataData($resultado->updated_at),
            ];
            array_push($contents, $conteudo);
        }
        $classes = [
            'table',
            'table-hover'
        ];

        $legenda = '<p><small><em><strong>Legenda:</strong></em>&nbsp;&nbsp;';
        foreach(self::cartoesImg() as $brand => $img)
            $legenda .= $brand . ' ' . $img . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $legenda .= '</small></p><hr />';
        $tabela = $legenda . montaTabela($headers, $contents, $classes);
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
                return '<span class="border rounded bg-success font-weight-bold p-1">Autorizado</span>';
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

        if(isset($this->combined_id))
        {
            $temp = self::where('combined_id', $this->combined_id)->where('id', '!=', $this->id)->first();
            if(!$temp->aprovado() && !$this->aprovado())
                return false;
        }
        elseif(!$this->aprovado())
            return false;

        $formato = strpos($this->authorized_at, '.') !== false ? 'Y-m-d\TH:i:s.uZ' : 'Y-m-d\TH:i:sZ';
        return $this->forma == 'combined' ? Carbon::createFromFormat($formato, $this->authorized_at)->addDays(7)->format('Y-m-d') >= Carbon::now('UTC')->format('Y-m-d') : 
            Carbon::createFromFormat($formato, $this->authorized_at)->format('Y-m-d') == Carbon::now('UTC')->format('Y-m-d');
    }

    public function getIdPagamento()
    {
        return $this->forma == 'combined' ? $this->combined_id : $this->payment_id;
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

    public function updateCancelamento($status, $message)
    {
        $this->update([
            'status' => is_array($status) ? $status[0] : $status,
            'canceled_at' => $message['dt'],
        ]);
    }

    public function updateAposErroGerenti($transacao)
    {
        $this->update([
            'gerenti_ok' => false,
            'transacao_temp' => json_encode($transacao, JSON_FORCE_OBJECT),
        ]);
    }

    public function updateAposSucessoGerenti()
    {
        $this->update([
            'gerenti_ok' => true,
            'transacao_temp' => null,
        ]);
    }

    public function updateAposNotificacao($dados)
    {
        $this->update([
            'status' => $dados['status'],
            'authorized_at' => in_array($dados['status'], ['APPROVED', 'CONFIRMED']) ? $dados['authorization_timestamp'] : $this->authorized_at,
            'canceled_at' => $dados['status'] == 'CANCELED' ? now()->toIso8601ZuluString() : $this->canceled_at,
        ]);
    }

    public function getCombinadoAposNotificacao($dados)
    {
        if(isset($this->combined_id))
            return self::where('boleto_id', $dados['order_id'])
                ->where('combined_id', $this->combined_id)
                ->where('payment_id', '!=', $dados['payment_id'])
                ->whereIn('status', ['APPROVED', 'CONFIRMED'])
                ->first();
        return null;
    }
}
