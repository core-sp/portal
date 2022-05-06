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

    public function getHorarios($horarios, $dia)
    {
        $inicialBloqueio = Carbon::parse($this->dataInicial);
        $finalBloqueio = Carbon::parse($this->dataFinal);
        $dia = Carbon::parse($dia);

        if($inicialBloqueio->lte($dia) && $finalBloqueio->gte($dia))
        {
            $horariosBloqueios = explode(',', $this->horarios);
            foreach($horariosBloqueios as $horario)
                unset($horarios[array_search($horario, $horarios)]);
        }

        return $horarios;
    }
}
