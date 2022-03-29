<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlantaoJuridico extends Model
{
    protected $table = 'plantoes_juridicos';
    protected $guarded = [];

    private function getHorariosComBloqueio($dia)
    {
        $horarios = explode(',', $this->horarios);

        if($this->bloqueios->count() > 0)
            foreach($this->bloqueios as $bloqueio)
            {
                $inicialBloqueio = Carbon::parse($bloqueio->dataInicial);
                $finalBloqueio = Carbon::parse($bloqueio->dataFinal);
                $dia = Carbon::parse($dia);

                if($inicialBloqueio->lte($dia) && $finalBloqueio->gte($dia))
                {
                    $horariosBloqueios = explode(',', $bloqueio->horarios);
                    foreach($horariosBloqueios as $horario)
                        unset($horarios[array_search($horario, $horarios)]);
                }
            }

        return $horarios;
    }

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

    public function getDiasSeLotado($agendados)
    {
        $diasLotados = array();

        foreach($agendados as $key => $agendado)
        {
            $dia = Carbon::parse($key);
            $horarios = $this->removeHorariosSeLotado($agendados[$dia->format('Y-m-d')], $dia->format('Y-m-d'));
            if(empty($horarios))
                array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
        }

        return $diasLotados;
    }

    public function removeHorariosSeLotado($agendado, $dia)
    {
        $horarios = $this->getHorariosComBloqueio($dia);

        if($agendado->isNotEmpty())
            foreach($agendado as $hora => $value)
                if($this->qtd_advogados == $value->count())
                    unset($horarios[array_search($hora, $horarios)]);
        
        return $horarios;
    }
}
