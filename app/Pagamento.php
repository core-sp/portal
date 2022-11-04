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
        }
    }

    public function getParcelas()
    {
        return $this->parcelas == '1' ? 'à vista' : $this->parcelas;
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
