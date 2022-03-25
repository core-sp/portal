<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        return $this->qtd_advogados > 0;
    }

    public function expirou()
    {
        return Carbon::parse($this->dataFinal)->lt(Carbon::today()) && $this->ativado();
    }

    public function getAgendadosPorPeriodo($inicio, $final)
    {
        $agendados = array();
        $inicial = Carbon::parse($inicio);
        $final = Carbon::parse($final);

        for($dia = $inicial; $dia->lte($final); $dia->addDay())
        {
            $agendado = $this->regional->agendamentos()
                ->select('hora', DB::raw('count(*) as total'))
                ->where('tiposervico', 'LIKE', 'Plantão Jurídico%')
                ->whereNull('status')
                ->where('dia', $dia->format('Y-m-d'))
                ->groupBy('hora')
                ->orderby('hora')
                ->get();
            if($agendado->isNotEmpty())
                $agendados[$dia->format('Y-m-d')] = $agendado;
        }
            
        return $agendados;
    }
}
