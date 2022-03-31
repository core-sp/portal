<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AgendamentoBloqueio extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idagendamentobloqueio';
    protected $table = 'agendamento_bloqueios';
    protected $guarded = [];

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function getMsgDiaTermino()
    {
        return isset($this->diatermino) ? onlyDate($this->diatermino) : 'Tempo Indeterminado';
    }

    public function getArrayHorarios($array, $dia)
    {
        $dia = Carbon::parse($dia);
        $inicialBloqueio = Carbon::parse($this->diainicio);
        $finalBloqueio = isset($this->diatermino) ? Carbon::parse($this->diatermino) : null;

        if($inicialBloqueio->lte($dia) && (!isset($finalBloqueio) || $finalBloqueio->gte($dia)))
        {
            $horariosBloqueios = explode(',', $this->horarios);
            foreach($horariosBloqueios as $horario)
            {
                if($this->qtd_atendentes == 0)
                    unset($array['horarios'][array_search($horario, $array['horarios'])]);
                else
                    $array['atendentes'][$horario] = $this->qtd_atendentes;
            }
        }

        return $array;
    }
}
