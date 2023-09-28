<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalaReuniaoBloqueio extends Model
{
    protected $table = 'salas_reunioes_bloqueios';
    protected $guarded = [];

    public function sala()
    {
    	return $this->belongsTo('App\SalaReuniao', 'sala_reuniao_id');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function mostraPeriodo()
    {
        $dataFinal = isset($this->dataFinal) ? onlyDate($this->dataFinal) : 'Tempo Indeterminado';

    	return onlyDate($this->dataInicial).' - '.$dataFinal;
    }

    public function getHorarios($horarios, $dia)
    {
        $dia = Carbon::parse($dia);
        $inicialBloqueio = Carbon::parse($this->dataInicial);
        $finalBloqueio = isset($this->dataFinal) ? Carbon::parse($this->dataFinal) : null;

        if($inicialBloqueio->lte($dia) && (!isset($finalBloqueio) || $finalBloqueio->gte($dia)))
        {
            $horariosBloqueios = explode(',', $this->horarios);
            foreach($horariosBloqueios as $horario)
                unset($horarios[array_search($horario, $horarios)]);
        }

        return $horarios;
    }
}
