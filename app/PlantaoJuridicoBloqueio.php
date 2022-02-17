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

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function podeEditar()
    {
        return Carbon::parse($this->plantaoJuridico->dataFinal)->gt(Carbon::today()) ? true : false;
    }
}
