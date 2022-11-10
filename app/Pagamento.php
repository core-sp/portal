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

    private static function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'Usuário',
            'Boleto',
            'Forma de pagamento',
            'Parcelas',
            'Tag <small>(mais de um cartão)</small>',
            'Status',
            'Enviado para o Gerenti',
            'Última alteração',
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) 
        {
            $conteudo = [
                $resultado->id,
                $resultado->getUser()->nome.'<br><small><em>'.formataCpfCnpj($resultado->getUser()->cpf_cnpj).'</em></small>',
                $resultado->boleto_id,
                $resultado->getForma(),
                $resultado->getParcelas(),
                $resultado->payment_tag,
                $resultado->getStatusLabel(),
                $resultado->gerenti_ok ? '<i class="fas fa-check text-success"></i> <em>Enviado</em>' : '<i class="fas fa-ban text-danger"></i> <em>Aguardando envio</em>',
                formataData($resultado->updated_at),
            ];
            array_push($contents, $conteudo);
        }
        $classes = [
            'table',
            'table-hover'
        ];

        $tabela = montaTabela($headers, $contents, $classes);
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
        return Carbon::createFromFormat($formato, $this->authorized_at)->day == Carbon::now('UTC')->day;
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
        if(isset($this->combined_id) && in_array($dados['status'], ['CANCELED', 'DENIED', 'ERROR']))
            return self::where('boleto_id', $dados['order_id'])
                ->where('combined_id', $this->combined_id)
                ->where('payment_id', '!=', $dados['payment_id'])
                ->whereIn('status', ['APPROVED', 'CONFIRMED'])
                ->first();
        return null;
    }
}
