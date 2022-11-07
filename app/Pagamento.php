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

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }

    public function aprovado()
    {
        return ($this->status == 'APPROVED') || ($this->status == 'AUTHORIZED');
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
            case 'FULL':
                return '';
            case 'INSTALL_NO_INTEREST':
                return 'sem juros';
            case 'INSTALL_WITH_INTEREST':
                return 'com juros';
        }
    }

    public function canCancel()
    {
        if(isset($this->combined_id))
        {
            $temp = self::where('combined_id', $this->combined_id)->where('id', '!=', $this->id)->first();
            if(!$temp->aprovado() && !$this->aprovado())
                return false;
        }
        elseif(!$this->aprovado())
            return false;

        return Carbon::createFromFormat('Y-m-d\TH:i:sZ', $this->authorized_at)->day == Carbon::now('UTC')->day;
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
}
