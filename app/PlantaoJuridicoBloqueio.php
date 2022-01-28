<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PlantaoJuridicoBloqueio extends Model
{
    protected $table = 'plantoes_juridicos_bloqueios';
    protected $guarded = [];

    public function plantaoJuridico()
    {
    	return $this->belongsTo('App\PlantaoJuridico', 'idplantaojuridico');
    }

    public function podeEditar()
    {
        return Carbon::parse($this->dataInicial)->gte(date('Y-m-d')) || Carbon::parse($this->dataFinal)->gte(date('Y-m-d')) ? true : false;
    }
}
