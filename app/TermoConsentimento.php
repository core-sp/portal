<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermoConsentimento extends Model
{
    use SoftDeletes;

    protected $table = 'termos_consentimentos';
    protected $guarded = [];

    const BENE_ALLYA = 'Allya';

    public static function beneficios()
    {
        return [
            self::BENE_ALLYA,
        ];
    }

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }

    public function newsletter()
    {
    	return $this->belongsTo('App\Newsletter', 'idnewsletter');
    }

    public function agendamento()
    {
    	return $this->belongsTo('App\Agendamento', 'idagendamento');
    }

    public function bdo()
    {
    	return $this->belongsTo('App\BdoOportunidade', 'idbdo');
    }

    public function cursoInscrito()
    {
    	return $this->belongsTo('App\CursoInscrito', 'idcursoinscrito');
    }

    public function agendamentoSala()
    {
    	return $this->belongsTo('App\AgendamentoSala', 'agendamento_sala_id');
    }

    public function message()
    {
        $message = 'foi criado um novo registro no termo de consentimento, com a id: '.$this->id;

        return isset($this->email) ? 'Novo email e '.$message : $message;
    }

    public function excluirBeneficio($ip)
    {
        $msg = 'a remoção da inscrição';
        if(!$this->trashed())
        {
            $this->update(['ip' => $ip]);
            return $this->delete() == 1 ? $msg : null;
        }
    }

    public function restaurarBeneficio($ip)
    {
        if($this->trashed())
        {
            $this->restore();
            $this->update(['ip' => $ip]);
            return 'novamente a inclusão da inscrição';
        }
    }
}
