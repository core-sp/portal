<?php

namespace App;

use App\Repositories\AgendamentoBloqueioRepository;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    public function getHorariosComBloqueio()
    {
        $bloqueios = $this->agendamentosBloqueios()
        ->where('diatermino', '>=', date('Y-m-d'))
        ->orWhere(function($query) {
            $query->where('idregional', $this->idregional)
            ->whereNull('diatermino');
        })->get();

        $resultado = [
            'horarios' => $this->horariosAge()
        ];

        if($bloqueios->isNotEmpty())
            foreach($bloqueios as $bloqueio)
            {
                $inicialBloqueio = Carbon::parse($bloqueio->diainicio);
                $finalBloqueio = isset($this->diatermino) ? Carbon::parse($bloqueio->diatermino) : null;
        
                if($inicialBloqueio->lte(Carbon::today()) && (!isset($finalBloqueio) || $finalBloqueio->gte(Carbon::today())))
                {
                    $horariosBloqueios = explode(',', $bloqueio->horarios);
                    foreach($horariosBloqueios as $horario)
                        if($bloqueio->qtd_atendentes == 0)
                            unset($resultado['horarios'][array_search($horario, $resultado['horarios'])]);
                        else
                            $resultado['atendentes'][$horario] = $bloqueio->qtd_atendentes;
                }
            }
        
        return $resultado;
    }

    public function getAgendadosPorPeriodo($inicio, $final)
    {
        return $this->agendamentos()
            ->select('dia', 'hora')
            ->where('tiposervico', 'NOT LIKE', 'Plantão Jurídico%')
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
        $horariosTotal = $this->getHorariosComBloqueio();

        foreach($agendados as $key => $agendado)
        {
            $dia = Carbon::parse($key);
            $horarios = $this->removeHorariosSeLotado($agendados[$dia->format('Y-m-d')], $dia->format('Y-m-d'), $horariosTotal);
            if(empty($horarios))
                array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
        }

        return $diasLotados;
    }

    public function removeHorariosSeLotado($agendado, $dia, $horarios)
    {
        if($agendado->isNotEmpty())
            foreach($agendado as $hora => $value)
            {
                $limiteBloqueio = isset($horarios['atendentes'][$hora]) && ($horarios['atendentes'][$hora] == $value->count());
                $limiteRegional = $this->ageporhorario == $value->count();
                if($limiteBloqueio || $limiteRegional)
                    unset($horarios['horarios'][array_search($hora, $horarios['horarios'])]);
            }
        
        return $horarios['horarios'];
    }
}
