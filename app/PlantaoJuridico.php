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
        $horarios = explode(',', $this->horarios);

        if($bloqueios->isNotEmpty())
            foreach($bloqueios as $bloqueio)
                $horarios = $bloqueio->getHorarios($horarios, $dia);

        return $horarios;
    }

    public function getAgendadosPorPeriodo($inicio, $final)
    {
        return $this->regional->agendamentos()
            ->select('dia', 'hora')
            ->where('tiposervico', 'LIKE', 'Plantão Jurídico%')
            ->whereNull('status')
            ->whereBetween('dia', [$inicio, $final])
            ->orderby('dia')
            ->orderby('hora')
            ->get()
            ->groupBy([
                'dia',
                function ($item) {
                    return $item['hora'];
                },
            ], $preserveKeys = false);
    }

    public function getDiasSeLotado()
    {
        $diasLotados = array();
        $bloqueios = $this->bloqueios;
        $agendados = $this->regional->agendamentos()
            ->select('dia', DB::raw('count(*) as total'))
            ->where('tiposervico', 'LIKE', 'Plantão Jurídico%')
            ->whereNull('status')
            ->whereBetween('dia', [$this->dataInicial, $this->dataFinal])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        foreach($agendados as $agendado)
        {
            $dia = Carbon::parse($agendado->dia);
            $horariosTotal = $this->getHorariosComBloqueio($bloqueios, $dia->format('Y-m-d'));
            $total = sizeof($horariosTotal) * $this->qtd_advogados;
            if($agendado->total >= $total)
                array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
        }

        return $diasLotados;
    }

    public function removeHorariosSeLotado($dia)
    {
        $bloqueios = $this->bloqueios;
        $horarios = $this->getHorariosComBloqueio($bloqueios, $dia);
        $agendado = $this->regional->agendamentos()
            ->select('dia', 'hora')
            ->where('tiposervico', 'LIKE', 'Plantão Jurídico%')
            ->whereNull('status')
            ->whereDate('dia', $dia)
            ->orderBy('dia')
            ->orderBy('hora')
            ->get()
            ->groupBy([
                'dia',
                function ($item) {
                    return $item['hora'];
                },
            ], $preserveKeys = false);

        if($agendado->isNotEmpty())
            foreach($agendado as $hora => $value)
                if($value->count() >= $this->qtd_advogados)
                    unset($horarios[array_search($hora, $horarios)]);
        
        return $horarios;
    }
}
