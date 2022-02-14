<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PlantaoJuridico extends Model
{
    protected $table = 'plantoes_juridicos';
    protected $guarded = [];

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function bloqueios()
    {
    	return $this->hasMany('App\PlantaoJuridicoBloqueio', 'idplantaojuridico');
    }

    public function ativado()
    {
        return $this->qtd_advogados > 0 ? true : false;
    }

    public function expirou()
    {
        return Carbon::parse($this->dataFinal)->lt(date('Y-m-d')) && $this->ativado() ? true : false;
    }
}
