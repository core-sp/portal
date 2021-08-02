<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TermoConsentimento extends Model
{
    protected $table = 'termos_consentimentos';
    protected $guarded = [];
    protected $with = ['representante', 'newsletter', 'agendamento', 'bdo'];

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
}
