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

    private function getHorariosComBloqueio($bloqueios, $dia)
    {
        if(Carbon::parse($dia)->isWeekend())
            return [];

        $horarios = explode(',', $this->horarios);

        if($bloqueios->isNotEmpty())
            foreach($bloqueios as $bloqueio)
                $horarios = $bloqueio->getHorarios($horarios, $dia);

        return $horarios;
    }

    public function getDiasSeLotado()
    {
        $diasLotados = array();
        $diaslotadosBloqueio = array();
        $bloqueios = $this->bloqueios;
        $agendados = $this->regional->agendamentos()
            ->select('dia', DB::raw('count(*) as total'))
            ->where('tiposervico', 'LIKE', 'Plantão Jurídico%')
            ->whereNull('status')
            ->whereBetween('dia', [$this->dataInicial, $this->dataFinal])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $inicial = Carbon::parse($this->dataInicial);
        $final = Carbon::parse($this->dataFinal);
        for($dia = $inicial; $inicial->lte($final); $dia->addDay())
        {
            $horariosTotal = $this->getHorariosComBloqueio($bloqueios, $dia->format('Y-m-d'));
            if(sizeof($horariosTotal) == 0)
            {
                array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
                array_push($diaslotadosBloqueio, $dia->format('Y-m-d'));
            }
        }

        foreach($agendados as $agendado)
        {
            $dia = Carbon::parse($agendado->dia);
            if(!isset($diaslotadosBloqueio[array_search($dia->format('Y-m-d'), $diaslotadosBloqueio)]))
            {
                $horariosTotal = $this->getHorariosComBloqueio($bloqueios, $dia->format('Y-m-d'));
                $total = sizeof($horariosTotal) * $this->qtd_advogados;
                if($agendado->total >= $total)
                    array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
            }
        }

        return $diasLotados;
    }

    public function removeHorariosSeLotado($dia)
    {
        $bloqueios = $this->bloqueios;
        $horarios = $this->getHorariosComBloqueio($bloqueios, $dia);

        if(sizeof($horarios) == 0)
            return $horarios;

        $agendado = $this->regional->agendamentos()
            ->select('hora', DB::raw('count(*) as total'))
            ->where('tiposervico', 'LIKE', 'Plantão Jurídico%')
            ->whereNull('status')
            ->whereDate('dia', $dia)
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        if($agendado->isNotEmpty())
            foreach($agendado as $value)
                if($value->total >= $this->qtd_advogados)
                    unset($horarios[array_search($value->hora, $horarios)]);
        
        return $horarios;
    }
}
