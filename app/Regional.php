<?php

namespace App;

use App\Repositories\AgendamentoBloqueioRepository;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Regional extends Model
{
    protected $table = 'regionais';
    protected $primaryKey = 'idregional';
    protected $guarded = [];
    public $timestamps = false;

    public function users()
    {
        return $this->hasMany('App\User', 'idregional')->withTrashed();
    }

    public function noticias()
    {
        return $this->hasMany('App\Noticia', 'idregional');
    }

    public function agendamentos()
    {
        return $this->hasMany('App\Agendamento', 'idregional');
    }

    public function agendamentosBloqueios()
    {
        return $this->hasMany('App\AgendamentoBloqueio', 'idregional');
    }

    public function plantaoJuridico()
    {
        return $this->hasOne('App\PlantaoJuridico', 'idregional');
    }

    public function horariosAge()
    {
        if(isset($this->horariosage))
            return explode(',', $this->horariosage);

        return [];
    }

    public function horariosDisponiveis($dia)
    {
        $horas = $this->horariosAge();
        $bloqueios = (new AgendamentoBloqueioRepository)->getByRegionalAndDay($this->idregional, $dia);
        if($bloqueios && $horas) {
            foreach($bloqueios as $bloqueio) {
                foreach($horas as $key => $hora) {
                    if($hora >= $bloqueio->horainicio && $hora <= $bloqueio->horatermino) {
                        unset($horas[$key]);
                    }
                }
            }
        }
        return $horas;
    }

    private function getAllBloqueios()
    {
        return $this->agendamentosBloqueios()
        ->where('diatermino', '>=', date('Y-m-d'))
        ->orWhere(function($query) {
            $query->where('idregional', $this->idregional)
            ->whereNull('diatermino');
        })->get();
    }

    private function getHorariosComBloqueio($bloqueios, $dia)
    {
        $resultado = [
            'horarios' => $this->horariosAge(),
        ];

        if($bloqueios->isNotEmpty())
            foreach($bloqueios as $bloqueio)
                $resultado = $bloqueio->getArrayHorarios($resultado, $dia);
        
        return $resultado;
    }

    private function getTotalAtendimentos($horariosTotal, $dia)
    {
        $total = 0;

        if(isset($horariosTotal['atendentes']))
            foreach($horariosTotal['atendentes'] as $hora => $value)
            {
                unset($horariosTotal['horarios'][array_search($hora, $horariosTotal['horarios'])]);
                $total += ($this->ageporhorario - $value);
            }
        $total += sizeof($horariosTotal['horarios']) * $this->ageporhorario;
        
        return $total;
    }

    public function getDiasSeLotado()
    {
        $diasLotados = array();
        $bloqueios = $this->getAllBloqueios();
        $agendados = $this->agendamentos()
            ->select('dia', DB::raw('count(*) as total'))
            ->where('tiposervico', 'NOT LIKE', 'Plantão Jurídico%')
            ->whereNull('status')
            ->whereBetween('dia', [Carbon::tomorrow()->format('Y-m-d'), Carbon::tomorrow()->addDays(30)->format('Y-m-d')])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        foreach($agendados as $agendado)
        {
            $dia = Carbon::parse($agendado->dia);
            $horariosTotal = $this->getHorariosComBloqueio($bloqueios, $dia->format('Y-m-d'));
            $total = $this->getTotalAtendimentos($horariosTotal, $dia->format('Y-m-d'));
            if($agendado->total >= $total)
                array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
        }

        return $diasLotados;
    }

    public function removeHorariosSeLotado($dia)
    {
        $bloqueios = $this->getAllBloqueios();
        $horarios = $this->getHorariosComBloqueio($bloqueios, $dia);
        $agendado = $this->agendamentos()
            ->select('dia', 'hora')
            ->where('tiposervico', 'NOT LIKE', 'Plantão Jurídico%')
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
            {
                $limiteBloqueio = isset($horarios['atendentes'][$hora]) && ($value->count() >= $horarios['atendentes'][$hora]);
                $limiteRegional = $value->count() >= $this->ageporhorario;
                if($limiteBloqueio || $limiteRegional)
                    unset($horarios['horarios'][array_search($hora, $horarios['horarios'])]);
            }
        
        return $horarios['horarios'];
    }
}
